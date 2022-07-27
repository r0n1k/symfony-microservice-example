<?php


namespace App\Domain\Project\UseCase\Conclusion\Paragraph\Block\SetCustomValue;


use App\Domain\Common\Flusher;
use App\Domain\Project\Entity\Conclusion\Paragraph\Block\Id;
use App\Domain\Project\Repository\Conclusion\Paragraph\Block\BlockRepository;

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

   public function handle(DTO $dto)
   {
      $block = $this->blocks->get(new Id($dto->block_id));
      $block->setCustomValue($dto->key, $dto->value);

      $this->flusher->flush();

      return $block;
   }
}
