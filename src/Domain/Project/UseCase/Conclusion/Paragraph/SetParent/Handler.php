<?php


namespace App\Domain\Project\UseCase\Conclusion\Paragraph\SetParent;

use App\Domain\Common\Flusher;
use App\Domain\Project\Entity\Conclusion\Paragraph\Id;
use App\Domain\Project\Entity\Conclusion\Paragraph\Order;
use App\Domain\Project\Entity\Conclusion\Paragraph\Paragraph;
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

   public function handle(DTO $dto): Paragraph
   {
      $paragraph = $this->paragraphs->get(new Id($dto->paragraph_id));
      $paragraph->setOrder(new Order($dto->order));
      if ($dto->parent_id === null) {
         $paragraph->setParent(null);
      } else {
         $parent = $this->paragraphs->get(new Id($dto->parent_id));
         $this->validateParent($paragraph, $parent);
         $paragraph->setParent($parent);
      }

      $this->paragraphs->add($paragraph);
      $this->flusher->flush();

      return $paragraph;
   }

   private function validateParent(Paragraph $paragraph, Paragraph $newParent)
   {
      if ($this->paragraphs->hasChild($paragraph, $newParent)) {
         throw new \DomainException('Circular tree are not allowed');
      }
   }
}
