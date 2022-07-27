<?php


namespace App\Domain\Project\UseCase\Conclusion\Paragraph\Rename;

use App\Domain\Common\Flusher;
use App\Domain\Project\Entity\Conclusion\Paragraph\Id;
use App\Domain\Project\Entity\Conclusion\Paragraph\Paragraph;
use App\Domain\Project\Repository\Conclusion\Paragraph\ParagraphRepository;
use App\Domain\Project\Entity\Conclusion\Paragraph\Title;

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

   public function handler(DTO $dto): Paragraph
   {
      $paragraph = $this->paragraphs->get(new Id($dto->paragraph_id));
      $paragraph->setTitle(new Title($dto->title));
      $this->paragraphs->add($paragraph);
      $this->flusher->flush();

      return $paragraph;
   }
}
