<?php


namespace App\Domain\Project\UseCase\Dictionary\UpsertProject;


use App\Domain\Common\Flusher;
use App\Domain\Project\Entity\Dictionary\Dictionary;
use App\Domain\Project\Entity\Dictionary\Key;
use App\Domain\Project\Entity\Project\Id as ProjectId;
use App\Domain\Project\Repository\Dictionary\DictionaryRepository;
use App\Domain\Project\Repository\Project\ProjectRepository;

class Handler
{

   /**
    * @var ProjectRepository
    */
   private ProjectRepository $projects;
   /**
    * @var DictionaryRepository
    */
   private DictionaryRepository $dictionaries;
   /**
    * @var Flusher
    */
   private Flusher $flusher;

   public function __construct(ProjectRepository $projects, DictionaryRepository $dictionaries, Flusher $flusher)
   {
      $this->projects = $projects;
      $this->dictionaries = $dictionaries;
      $this->flusher = $flusher;
   }

   public function handle(DTO $dto): Dictionary
   {
      $project = $this->projects->get(new ProjectId($dto->project_id));

      $key = new Key($dto->dictionary_key);
      if (!($dictionary = $this->dictionaries->findByProjectAndKey($project, $key)) instanceof Dictionary) {
         $id = $this->dictionaries->nextId();
         $dictionary = new Dictionary($id, $key, $project, null, null, $dto->value);
         $this->dictionaries->add($dictionary);
      } else {
         $dictionary->setValue($dto->value);
      }

      $this->flusher->flush();
      return $dictionary;
   }

}
