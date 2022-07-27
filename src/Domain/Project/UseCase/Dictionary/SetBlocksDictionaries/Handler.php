<?php


namespace App\Domain\Project\UseCase\Dictionary\SetBlocksDictionaries;


use App\Domain\Common\Flusher;
use App\Domain\Project\Entity\Conclusion\Paragraph\Block\Block;
use App\Domain\Project\Entity\Conclusion\Paragraph\Block\Id as BlockId;
use App\Domain\Project\Entity\Dictionary\Dictionary;
use App\Domain\Project\Entity\Dictionary\Key;
use App\Domain\Project\Entity\Project\Project;
use App\Domain\Project\Repository\Conclusion\Paragraph\Block\BlockRepository;
use App\Domain\Project\Repository\Dictionary\DictionaryRepository;
use App\Domain\Project\Repository\Project\ProjectRepository;
use App\Domain\Project\Service\DictionaryKeyTranslator;
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
    * @var DictionaryKeyTranslator
    */
   private DictionaryKeyTranslator $translator;

   /**
    * Handler constructor.
    * @param ProjectRepository $projects
    * @param DictionaryRepository $dictionaries
    * @param BlockRepository $blocks
    * @param DictionaryKeyTranslator $translator
    * @param Flusher $flusher
    */
   public function __construct(ProjectRepository $projects,
                               DictionaryRepository $dictionaries,
                               BlockRepository $blocks,
                               DictionaryKeyTranslator $translator,
                               Flusher $flusher)
   {
      $this->projects = $projects;
      $this->dictionaries = $dictionaries;
      $this->blocks = $blocks;
      $this->flusher = $flusher;
      $this->translator = $translator;
   }

   /**
    * @param DTO $dto
    * @return Dictionary[]
    */
   public function handle(DTO $dto): array
   {
      $block = $this->blocks->get(new BlockId($dto->block_id));
      $project = $this->projects->getByBlock($block);

      $this->cleanUpDictionaries($block);
      $this->flusher->flush();

      $resultDictionaries = [];
      foreach ($dto->keys as $key) {
         $resultDictionaries[] = $this->createDictionary($project, $block, $key);
      }

      $this->flusher->flush();

      return $resultDictionaries;
   }

   /**
    * @param Project $project
    * @param Block $block
    * @param string $keyString
    * @return Dictionary
    */
   private function createDictionary(Project $project, Block $block, string $keyString): Dictionary
   {
      if (!$this->dictionaries->findByProjectAndKey($project, new Key($keyString))) {
         throw new DomainException("Project's dictionary $keyString does not exists");
      }
      $nextId = $this->dictionaries->nextId();
      $key = new Key($keyString);
      $name = $this->translator->translate($key);
      $dictionary = new Dictionary($nextId, $key, $project, $block, $name);
      $this->dictionaries->add($dictionary);
      return $dictionary;
   }

   /**
    * @param Block $block
    */
   private function cleanUpDictionaries(Block $block)
   {
      /** @var Dictionary[] $dictionaries */
      $dictionaries = $this->dictionaries->findByBlock($block);
      foreach ($dictionaries as $dictionary) {
         $this->dictionaries->remove($dictionary);
      }
   }

}
