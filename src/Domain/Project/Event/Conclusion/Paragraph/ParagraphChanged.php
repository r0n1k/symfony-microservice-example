<?php


namespace App\Domain\Project\Event\Conclusion\Paragraph;


use App\Domain\Common\DomainEvent;
use App\Domain\Project\Entity\Conclusion\Paragraph\Paragraph;

class ParagraphChanged extends DomainEvent
{

   public function __construct(Paragraph $paragraph)
   {
      $this->entity = $paragraph;
      parent::__construct();
   }

   /**
    * @return Paragraph
    */
   public function getParagraph(): Paragraph
   {
      return $this->entity;
   }

}
