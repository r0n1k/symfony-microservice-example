<?php


namespace App\Domain\Project\UseCase\Conclusion\Paragraph\Create;

use App\Domain\Common\Flusher;
use App\Domain\Project\Repository\Conclusion\ConclusionRepository;
use App\Domain\Project\Entity\Conclusion\Paragraph\Id;
use App\Domain\Project\Entity\Conclusion\Paragraph\Order;
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
   /**
    * @var ConclusionRepository
    */
   private ConclusionRepository $conclusions;

   public function __construct(ParagraphRepository $paragraphs, ConclusionRepository $conclusions, Flusher $flusher)
   {
      $this->paragraphs = $paragraphs;
      $this->flusher = $flusher;
      $this->conclusions = $conclusions;
   }

   public function handle(DTO $dto): Paragraph
   {
      $conclusion = $this->conclusions->get($dto->conclusion_id);
      $title = new Title($dto->title);
      if ($dto->parent_id) {
         $parent = $this->paragraphs->get(new Id($dto->parent_id));
      } else {
         $parent = null;
      }
      $order = new Order($dto->order);

      $id = $this->paragraphs->nextId();
      if ($parent instanceof Paragraph) {
         $paragraph = $parent->addChild($id, $title, $order);
      } else {
         $paragraph = $conclusion->addParagraph($id, $title, $order);
      }

      $this->conclusions->add($conclusion);
      $this->flusher->flush();

      return $paragraph;
   }
}
