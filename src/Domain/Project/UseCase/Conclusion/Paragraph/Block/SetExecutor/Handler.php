<?php

namespace App\Domain\Project\UseCase\Conclusion\Paragraph\Block\SetExecutor;

use App\Domain\Common\Flusher;
use App\Domain\Project\Entity\Conclusion\Paragraph\Block\Block;
use App\Domain\Project\Repository\Conclusion\Paragraph\Block\BlockRepository;
use App\Domain\Project\Entity\Conclusion\Paragraph\Block\Id;
use App\Domain\Project\Entity\Users\User\Id as UserId;
use App\Domain\Project\Repository\Users\User\UserRepository;

class Handler
{
   private BlockRepository $blocks;
   private Flusher $flusher;
   private UserRepository $users;

   public function __construct(BlockRepository $blocks, UserRepository $users, Flusher $flusher)
   {
      $this->blocks = $blocks;
      $this->flusher = $flusher;
      $this->users = $users;
   }

   public function handle(DTO $dto): Block
   {
      $block = $this->blocks->get(new Id($dto->block_id));
      $executor = $dto->user_id
         ? $this->users->get(new UserId($dto->user_id))
         : null;

      $block->setExecutor($executor);

      $this->blocks->add($block);
      $this->flusher->flush();

      return $block;
   }
}
