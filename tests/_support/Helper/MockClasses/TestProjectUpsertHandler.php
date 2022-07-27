<?php


namespace App\Tests\Helper\MockClasses;


use App\Domain\Common\Flusher;
use App\Domain\Project\Entity\Project\Id;
use App\Domain\Project\Entity\Project\Project;
use App\Domain\Project\Repository\Certificate\CertificateRepository;
use App\Domain\Project\Repository\Dictionary\DictionaryRepository;
use App\Domain\Project\Repository\Project\ProjectRepository;
use App\Domain\Project\Repository\Users\ProjectUserAssignment\ProjectUserAssignmentRepository;
use App\Domain\Project\Repository\Users\User\UserRepository;
use App\Domain\Project\Service\DictionaryKeyTranslator;
use App\Domain\Project\UseCase\Project\Upsert\DTO;
use App\Domain\Project\UseCase\Project\Upsert\Handler;

class TestProjectUpsertHandler extends Handler
{
   protected ProjectRepository $projects;
   public function __construct(ProjectRepository $projects, UserRepository $users, ProjectUserAssignmentRepository $assignments, DictionaryRepository $dictionaries, CertificateRepository $certificates, DictionaryKeyTranslator $translator, Flusher $flusher)
   {
      parent::__construct($projects, $users, $assignments, $dictionaries, $certificates, $translator, $flusher);
      $this->projects = $projects;
   }

   public function handle(DTO $dto): Project
   {
      return $this->projects->get(new Id($dto->project_id));
   }

}
