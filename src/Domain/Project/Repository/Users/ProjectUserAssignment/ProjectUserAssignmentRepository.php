<?php


namespace App\Domain\Project\Repository\Users\ProjectUserAssignment;

use App\Domain\Project\Entity\Conclusion\Conclusion;
use App\Domain\Project\Entity\Project\Project;
use App\Domain\Project\Entity\Users\ProjectUserAssignment\ProjectUserAssignment;
use App\Domain\Project\Entity\Users\User\User;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\Persistence\ObjectRepository;

class ProjectUserAssignmentRepository
{

   /**
    * @var ObjectRepository
    */
   private ObjectRepository $repo;
   /**
    * @var EntityManagerInterface
    */
   private EntityManagerInterface $em;

   public function __construct(EntityManagerInterface $em)
   {
      $this->repo = $em->getRepository(ProjectUserAssignment::class);
      $this->em = $em;
   }

   /**
    * @param Project $project
    * @param User $user
    * @return ProjectUserAssignment|null
    */
   public function findForProjectAndUser(Project $project, User $user)
   {
      /** @var ProjectUserAssignment $assignment */
      $assignment = $this->repo->findOneBy(['user' => $user, 'project' => $project]);
      return $assignment;
   }

   public function add(ProjectUserAssignment $assignment)
   {
      if ($project = $assignment->getProject()) {
         $project->addUser($assignment);
      }
      if ($user = $assignment->getUser()) {
         $user->addProject($assignment);
      }
      $this->em->persist($assignment);
   }

   public function remove(ProjectUserAssignment $assignment)
   {
      if ($user = $assignment->getUser()) {
         $user->removeProject($assignment);
      }

      if ($project = $assignment->getProject()) {
         $project->removeUser($assignment);
      }

      $this->em->remove($assignment);
   }

   /**
    * @param User $user
    * @param Conclusion $conclusion
    * @return ProjectUserAssignment|null
    * @noinspection PhpDocMissingThrowsInspection
    */
   public function findForUserAndConclusion(User $user, Conclusion $conclusion): ?ProjectUserAssignment
   {
      /** @noinspection PhpUnhandledExceptionInspection */
      return $this->em->createQueryBuilder()
         ->select('a')
         ->from(Conclusion::class, 'c')
         ->leftJoin('c.project', 'p')
         ->leftJoin('p.users', 'a')
         ->leftJoin('a.user', 'u')
         ->where('c.id = :conclusion_id')
         ->andWhere('u.id = :user_id')
         ->setParameter(':conclusion_id', $conclusion->getId())
         ->setParameter(':user_id', $user->getId())
         ->getQuery()
         ->getSingleResult();
   }

}
