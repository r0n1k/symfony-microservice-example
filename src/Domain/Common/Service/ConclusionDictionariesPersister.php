<?php


namespace App\Domain\Common\Service;


use App\Domain\Project\Entity\Conclusion\Conclusion;
use App\Domain\Project\Entity\Conclusion\Paragraph\Paragraph;
use App\Domain\Project\Repository\Conclusion\Paragraph\ParagraphRepository;

class ConclusionDictionariesPersister
{

   /**
    * @var ParagraphRepository
    */
   private ParagraphRepository $paragraphs;

   public function __construct(ParagraphRepository $paragraphs)
   {
      $this->paragraphs = $paragraphs;
   }

   /**
    * @param Conclusion $conclusion
    * @return void
    */
   public function persist(Conclusion $conclusion)
   {
   }

   private function persistParagraph(Paragraph $paragraph, array $values)
   {
   }

}
