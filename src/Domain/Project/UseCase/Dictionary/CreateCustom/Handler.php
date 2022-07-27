<?php


namespace App\Domain\Project\UseCase\Dictionary\CreateCustom;


use App\Domain\Common\Flusher;
use App\Domain\Project\Entity\Conclusion\Paragraph\Block\Id as BlockId;
use App\Domain\Project\Entity\Dictionary\Dictionary;
use App\Domain\Project\Entity\Dictionary\Key;
use App\Domain\Project\Entity\Project\Id as ProjectId;
use App\Domain\Project\Repository\Conclusion\Paragraph\Block\BlockRepository;
use App\Domain\Project\Repository\Dictionary\DictionaryRepository;
use App\Domain\Project\Repository\Project\ProjectRepository;
use DomainException;

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
    * @var BlockRepository
    */
   private BlockRepository $blocks;
   /**
    * @var Flusher
    */
   private Flusher $flusher;

   /**
    * Handler constructor.
    * @param ProjectRepository $projects
    * @param DictionaryRepository $dictionaries
    * @param BlockRepository $blocks
    * @param Flusher $flusher
    */
   public function __construct(ProjectRepository $projects,
                               DictionaryRepository $dictionaries,
                               BlockRepository $blocks,
                               Flusher $flusher)
   {
      $this->projects = $projects;
      $this->dictionaries = $dictionaries;
      $this->blocks = $blocks;
      $this->flusher = $flusher;
   }

   /**
    * @param DTO $dto
    * @return Dictionary
    */
   public function handle(DTO $dto): Dictionary
   {
      $this->validateDTO($dto);
      $project = $this->projects->get(new ProjectId($dto->project_id));

      $key = new Key($dto->key);
      if ($this->dictionaries->findByProjectAndKey($project, $key)) {
         throw new DomainException('Dictionary already exists');
      }

      if ($dto->block_id) {
         $block = $this->blocks->get(new BlockId($dto->block_id));
      }

      $id = $this->dictionaries->nextId();
      $dict = new Dictionary($id, $key, $project, $block ?? null, $dto->name, $dto->value);
      $this->dictionaries->add($dict);
      $this->flusher->flush();

      return $dict;
   }

   private function validateDTO(DTO $dto)
   {
   }

}
