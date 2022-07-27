<?php


namespace App\Domain\Project\UseCase\Dictionary\Delete;


use App\Domain\Common\Flusher;
use App\Domain\Project\Entity\Conclusion\Paragraph\Block\Id as BlockId;
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
    * @var BlockRepository
    */
   private BlockRepository $blocks;
   /**
    * @var DictionaryRepository
    */
   private DictionaryRepository $dictionaries;
   /**
    * @var Flusher
    */
   private Flusher $flusher;

   public function __construct(ProjectRepository $projects,
                               BlockRepository $blocks,
                               DictionaryRepository $dictionaries,
                               Flusher $flusher)
   {
      $this->projects = $projects;
      $this->blocks = $blocks;
      $this->dictionaries = $dictionaries;
      $this->flusher = $flusher;
   }

   public function handle(DTO $dto)
   {
      $project = $this->projects->get(new ProjectId($dto->project_id));
      $key = new Key($dto->dictionary_key);
      if ($dto->block_id) {
         $block = $this->blocks->get(new BlockId($dto->block_id));
         $dictionary = $this->dictionaries->findByBlockAndKey($block, $key);
      } else {
         $dictionary = $this->dictionaries->findByProjectAndKey($project, $key);
      }

      if (!$dictionary) {
         throw new DomainException('Dictionary not found');
      }

      $this->dictionaries->remove($dictionary);
      $this->flusher->flush();
   }

}
