<?php


namespace App\Domain\Project\UseCase\Project\Upsert;

use App\Domain\Common\Flusher;
use App\Domain\Project\Entity\Dictionary\Dictionary;
use App\Domain\Project\Entity\Dictionary\Key;
use App\Domain\Project\Entity\Project\Name;
use App\Domain\Project\Entity\Project\State;
use App\Domain\Project\Entity\Users\ProjectUserAssignment\ProjectUserAssignment;
use App\Domain\Project\Entity\Users\User\Certificate\Certificate;
use App\Domain\Project\Entity\Users\User\Certificate\Scope;
use App\Domain\Project\Repository\Certificate\CertificateRepository;
use App\Domain\Project\Repository\Dictionary\DictionaryRepository;
use App\Domain\Project\Repository\Users\ProjectUserAssignment\ProjectUserAssignmentRepository;
use App\Domain\Project\Entity\Users\ProjectUserAssignment\Role as AssignmentRole;
use App\Domain\Project\Entity\Users\User\FullName;
use App\Domain\Project\Entity\Users\User\Id as UserId;
use App\Domain\Project\Entity\Users\User\Role as UserRole;
use App\Domain\Project\Entity\Users\User\Email;
use App\Domain\Project\Repository\Users\User\UserRepository;
use App\Domain\Project\Entity\Project\Id;
use App\Domain\Project\Repository\Project\ProjectRepository;
use App\Domain\Project\Entity\Project\Project;
use App\Domain\Project\Entity\Users\User\User;
use App\Domain\Project\Service\DictionaryKeyTranslator;

class Handler
{

   /**
    * @var ProjectRepository
    */
   private ProjectRepository $projectRepo;
   /**
    * @var Flusher
    */
   private Flusher $flusher;
   /**
    * @var UserRepository
    */
   private UserRepository $userRepo;
   /**
    * @var ProjectUserAssignmentRepository
    */
   private ProjectUserAssignmentRepository $assignments;
   /**
    * @var DictionaryRepository
    */
   private DictionaryRepository $dictionaries;
   /**
    * @var DictionaryKeyTranslator
    */
   private DictionaryKeyTranslator $translator;
   /**
    * @var CertificateRepository
    */
   private CertificateRepository $certificates;

   public function __construct(
       ProjectRepository $projects,
       UserRepository $users,
       ProjectUserAssignmentRepository $assignments,
       DictionaryRepository $dictionaries,
       CertificateRepository $certificates,
       DictionaryKeyTranslator $translator,
       Flusher $flusher
   ) {
      $this->projectRepo = $projects;
      $this->userRepo = $users;
      $this->flusher = $flusher;
      $this->assignments = $assignments;
      $this->dictionaries = $dictionaries;
      $this->translator = $translator;
      $this->certificates = $certificates;
   }

   /**
    * @param DTO $dto
    * @return Project
    */
   public function handle(DTO $dto): Project
   {

      $project = $this->upsertProject($dto);
      $this->upsertCertificates($dto->certificates);
      $this->upsertProjectUsers($project, $dto->users);
      $this->upsertProjectDictionaries($project, $dto->dictionaries);

      $this->flusher->flush();

      return $project;
   }

   private function upsertProject(DTO $dto)
   {

      $projectId = new Id($dto->project_id);
      $projectName = new Name($dto->project_name);
      $projectState = new State($dto->project_state);

      /**
       * @var Project $project
       */
      $project = $this->projectRepo->find(new Id($dto->project_id)) ??
         new Project($projectId, $projectName, $projectState);

      $project
         ->setName($projectName)
         ->setState($projectState);

      $this->projectRepo->add($project);

      return $project;
   }

   /**
    * @param Project $project
    * @param UserDTO[] $userDTOS
    */
   private function upsertProjectUsers(Project $project, array $userDTOS)
   {
      $this->removeUnusedAssignments($project, $userDTOS);

      foreach ($userDTOS as $dto) {
         $user = $this->upsertUser($dto);
         $assignment =
            $this->assignments->findForProjectAndUser($project, $user) ??
            new ProjectUserAssignment($project, $user, new AssignmentRole($dto->assignment_type));

         // тк при смене ведущего/обычного экперта роль никогда не изменится
         $assignment->setRole(new AssignmentRole($dto->assignment_type));

         $this->assignments->add($assignment);
      }
   }

   private function removeUnusedAssignments(Project $project, array $users)
   {
      $userAssignments = $project->getUsers();

      $userIds = array_map(static function (UserDTO $userDTO) {
         return (string)$userDTO->id;
      }, $users);

      $unusedAssignments = $userAssignments->filter(
          static function (ProjectUserAssignment $assignment) use ($userIds) {
              $user = $assignment->getUser();
              return $user !== null && !in_array((string)$user->getId(), $userIds, true);
          }
      );

      foreach ($unusedAssignments as $assignment) {
         $this->assignments->remove($assignment);
      }
   }

   private function upsertUser(UserDTO $dto): User
   {
      $userId = new UserId($dto->id);
      $userRole = new UserRole($dto->role);
      $userEmail = new Email($dto->email);
      $userFullName = new FullName($dto->full_name);

      $user = $this->userRepo->find($userId) ??
         new User($userId, $userFullName, $userEmail, $userRole);

      $user->setRole($userRole);
      $user->setEmail($userEmail);
      $user->setFullName($userFullName);

      foreach ($dto->certificates as $userCert) {
         $certificate = $this->certificates->findByScope($userCert->scope);
         if ($certificate instanceof Certificate) {
            $user->addCertificate($certificate);
         }
      }
      return $user;
   }

   private function upsertProjectDictionaries(Project $project, DictionaryDTO $dictionaries)
   {
      foreach ($dictionaries as $key => $value)
      {
         $baseKey = $key;
         foreach ($this->getDictionaryValuePaths($value, $baseKey) as $dictKey => $dictVal) {
            $key = new Key($dictKey);
            if (!$this->dictionaries->findByProjectAndKey($project, $key)) {
               $nextId = $this->dictionaries->nextId();
               $name = $this->translator->translate($key);
               $dictionary = new Dictionary($nextId, $key, $project, null, $name, $dictVal);
               $this->dictionaries->add($dictionary);
            }
         }
      }
   }

   /**
    * @param $value
    * @param string $baseKey
    * @return iterable
    */
   private function getDictionaryValuePaths($value, string $baseKey): iterable
   {
      $result = [];
      if (is_array($value)) {
         foreach ($value as $key => $subvalue) {
            /** @noinspection SlowArrayOperationsInLoopInspection */
            $result = array_merge($result, $this->getDictionaryValuePaths($subvalue, "$baseKey.$key"));
         }
      } else {
         $result[$baseKey] = $value;
      }
      return $result;
   }

   private function upsertCertificates(array $certificates)
   {
      foreach ($certificates as $scope) {
         if (!$this->certificates->findByScope($scope)) {
            $certificate = new Certificate($this->certificates->nextId(), Scope::of($scope));
            $this->certificates->add($certificate);
         }
      }
   }
}
