<?php


namespace App\Domain\Project\UseCase\Conclusion\Paragraph\Delete;

use App\Domain\Common\Flusher;
use App\Domain\Project\Entity\Conclusion\Paragraph\Id;
use App\Domain\Project\Repository\Conclusion\Paragraph\ParagraphRepository;

class Handler
{

   /**
    * @var ParagraphRepository
    */
   private ParagraphRepository $paragraphs;
   /**
    * @var Flusher
    */
   private Flusher $flusher;

   public function __construct(ParagraphRepository $paragraphs, Flusher $flusher)
   {
      $this->paragraphs = $paragraphs;
      $this->flusher = $flusher;
   }

   public function handler(DTO $dto)
   {
      $paragraph = $this->paragraphs->get(new Id($dto->paragraph_id));
      $paragraph->getConclusion()->removeParagraph($paragraph->getId());
      $this->paragraphs->remove($paragraph);
      $this->flusher->flush();
   }
}
