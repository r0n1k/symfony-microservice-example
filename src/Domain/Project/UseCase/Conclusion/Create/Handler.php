<?php


namespace App\Domain\Project\UseCase\Conclusion\Create;

use App\Domain\Common\Flusher;
use App\Domain\Common\Service\ConclusionDictionariesPersister;
use App\Domain\Common\Service\TemplateBootstrapper;
use App\Domain\Project\Entity\Conclusion\Conclusion;
use App\Domain\Project\Entity\Conclusion\Id;
use App\Domain\Project\Entity\Conclusion\Kind;
use App\Domain\Project\Entity\Conclusion\Revision;
use App\Domain\Project\Entity\Conclusion\Title;
use App\Domain\Project\Entity\Project\Id as ProjectId;
use App\Domain\Project\Entity\Project\Project;
use App\Domain\Project\Entity\Users\User\Id as UserId;
use App\Domain\Project\Repository\Conclusion\ConclusionRepository;
use App\Domain\Project\Repository\Project\ProjectRepository;
use App\Domain\Project\Repository\Users\User\UserRepository;
use App\Domain\Template\Repository\TemplateRepository;

class Handler
{
   private ProjectRepository $projects;
   private ConclusionRepository $conclusions;
   private Flusher $flusher;
   private UserRepository $users;
   private TemplateBootstrapper $templateBootstrapper;
   private TemplateRepository $templates;
   private ConclusionDictionariesPersister $dictionariesPersister;


   public function __construct(
       ProjectRepository $projects,
       ConclusionRepository $conclusions,
       TemplateBootstrapper $mapper,
       TemplateRepository $templates,
       Flusher $flusher,
       ConclusionDictionariesPersister $dictionariesPersister,
       UserRepository $users
   )
   {
      $this->projects = $projects;
      $this->conclusions = $conclusions;
      $this->flusher = $flusher;
      $this->users = $users;
      $this->templateBootstrapper = $mapper;
      $this->templates = $templates;
      $this->dictionariesPersister = $dictionariesPersister;
   }

   public function handle(DTO $dto): Conclusion
   {
      $project = $this->projects->get(new ProjectId($dto->project_id));

      $author = $this->users->get(new UserId($dto->author_id));

      /** @var Conclusion|null $latestConclusion */
      $latestConclusion = $this->conclusions->findLatestForProject($project);
      $latestRevision = $latestConclusion ? $latestConclusion->getRevision() : new Revision(0);

      $conclusionId = Id::next();
      $title = new Title($dto->name);
      $kind = new Kind($dto->kind);
      $conclusion = new Conclusion(
          $conclusionId,
          $title,
          $author,
          $project,
          $kind,
          $latestRevision->next(),
          null,
          null,
          $dto->is_local
      );

      if ($latestConclusion instanceof Conclusion) {
         $this->dictionariesPersister->persist($latestConclusion);
      }

      if ($dto->template_id) {
         $template = $this->templates->get($dto->template_id);
         $this->templateBootstrapper->bootstrap($template, $conclusion);
      }

      $this->conclusions->add($conclusion);
      $this->flusher->flush();

      return $conclusion;
   }
}
