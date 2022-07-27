<?php


namespace App\Domain\Project\UseCase\Conclusion\Paragraph\Block\Delete;

use App\Domain\Common\Flusher;
use App\Domain\Project\Entity\Conclusion\Paragraph\Block\State;
use App\Domain\Project\Repository\Conclusion\Paragraph\Block\BlockRepository;
use App\Domain\Project\Entity\Conclusion\Paragraph\Block\Id;

class Handler
{

   private Flusher $flusher;
   private BlockRepository $blocks;

   public function __construct(BlockRepository $blocks, Flusher $flusher)
   {
      $this->blocks = $blocks;
      $this->flusher = $flusher;
   }

   public function handle(DTO $dto)
   {
       $blocks = [];

       foreach ($dto->block_ids as $blockId) {
           $block = $this->blocks->get(new Id($blockId));
           $block->setState(State::deleted());

           $this->blocks->add($block);

           $blocks[] = $block;
       }

       $this->flusher->flush();

       return $blocks;
   }
}
