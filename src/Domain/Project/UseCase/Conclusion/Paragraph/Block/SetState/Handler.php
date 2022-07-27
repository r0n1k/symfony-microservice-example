<?php

namespace App\Domain\Project\UseCase\Conclusion\Paragraph\Block\SetState;

use App\Domain\Common\Flusher;
use App\Domain\Project\Repository\Conclusion\Paragraph\Block\BlockRepository;
use App\Domain\Project\Entity\Conclusion\Paragraph\Block\Id;
use App\Domain\Project\Entity\Conclusion\Paragraph\Block\State;
use Webmozart\Assert\Assert;


class Handler
{

   /**
    * @var BlockRepository
    */
   private BlockRepository $blocks;
   /**
    * @var Flusher
    */
   private Flusher $flusher;

   public function __construct(BlockRepository $blocks, Flusher $flusher)
   {
      $this->blocks = $blocks;
      $this->flusher = $flusher;
   }

   public function handle(DTO $dto): array
   {
       $blocks = [];

       $newState = new State($dto->new_state);

       foreach ($dto->block_ids as $blockId){
           $block = $this->blocks->get(new Id($blockId));

           switch ($newState->getValue()) {
               case State::DECLINED: {
                   if ($block->getState()->getValue() === State::ON_REVIEW) {
                       $declineReason = $dto->decline_reason;
                       Assert::stringNotEmpty($declineReason, 'Decline reason isn\'t set');
                       $block->setDeclineReason($declineReason);
                   }
                   break;
               }

               case State::COMPLETED: {
                   $block->setDeclineReason(null);
                   break;
               }
           }

           $block->setState($newState);

           $this->blocks->add($block);

           $blocks[] = $block;
       }

       $this->flusher->flush();

       return $blocks;
   }
}
