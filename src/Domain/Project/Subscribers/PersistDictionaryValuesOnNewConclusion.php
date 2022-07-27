<?php


namespace App\Domain\Project\Subscribers;


use App\Domain\Common\DomainEvent;
use App\Domain\Common\DomainEventSubscriber;
use App\Domain\Common\Flusher;
use App\Domain\Project\Entity\Conclusion\Conclusion;
use App\Domain\Project\Entity\Conclusion\Paragraph\Block\Block;
use App\Domain\Project\Event\Conclusion\ConclusionCreated;
use App\Domain\Project\Repository\Conclusion\ConclusionRepository;
use App\Domain\Project\Repository\Conclusion\Paragraph\Block\BlockRepository;
use App\Domain\Project\Repository\Dictionary\DictionaryRepository;
use App\Domain\Project\Service\DictionaryValueFetcher;
use Throwable;

class PersistDictionaryValuesOnNewConclusion implements DomainEventSubscriber
{

   /**
    * @var ConclusionRepository
    */
   private ConclusionRepository $conclusions;
   /**
    * @var BlockRepository
    */
   private BlockRepository $blocks;
   /**
    * @var DictionaryRepository
    */
   private DictionaryRepository $dictionaries;
   /**
    * @var DictionaryValueFetcher
    */
   private DictionaryValueFetcher $fetcher;
   /**
    * @var Flusher
    */
   private Flusher $flusher;

   public function __construct(ConclusionRepository $conclusions,
                               BlockRepository $blocks,
                               DictionaryValueFetcher $fetcher,
                               Flusher $flusher,
                               DictionaryRepository $dictionaries)
   {
      $this->conclusions = $conclusions;
      $this->blocks = $blocks;
      $this->dictionaries = $dictionaries;
      $this->fetcher = $fetcher;
      $this->flusher = $flusher;
   }

   public function handle(DomainEvent $event): void
   {
      if (!$event instanceof ConclusionCreated) {
         return;
      }

      $conclusion = $event->getConclusion();

      if (!$conclusion->getRevision()->isFirst()) {
         try {
            $prevConclusion = $this->conclusions->getPrevious($conclusion);
         } catch (Throwable $e) {
            return;
         }
         $this->persist($prevConclusion);
      }
   }

   private function persist(Conclusion $conclusion)
   {
      $blocks = $this->blocks->findAllByConclusion($conclusion);

      foreach ($blocks as $block) {
         $this->persistBlock($block);
      }

      $this->flusher->flush();
   }

   private function persistBlock(Block $block)
   {
      $dictionaries = $this->dictionaries->findByBlock($block);
      foreach ($dictionaries as $dict) {
         if ($dict->getValue() === null) {
            $project = $block->getParagraph()->getConclusion()->getProject();
            $dict->setValue($this->fetcher->fetchByProjectAndKey($project, $dict->getKey()));
         }
      }
   }

}
