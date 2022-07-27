<?php


namespace App\Domain\Project\Event\Conclusion\Paragraph\Block;


use App\Domain\Common\DomainEvent;
use App\Domain\Project\Entity\Conclusion\Paragraph\Block\Block;

class BlockDeleted extends DomainEvent
{

   public function __construct(Block $block)
   {
      $this->entity = $block;
      parent::__construct();
   }

   public function getBlock(): Block
   {
      return $this->entity;
   }


}
