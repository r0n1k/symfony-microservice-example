<?php


namespace App\Domain\Project\UseCase\Conclusion\Paragraph\Block\SetKind;

use App\Domain\Common\Flusher;
use App\Domain\Common\Service\BlockFilePathResolverInterface;
use App\Domain\Project\Entity\Conclusion\Paragraph\Block\Block;
use App\Domain\Project\Repository\Conclusion\Paragraph\Block\BlockRepository;
use App\Domain\Project\Entity\Conclusion\Paragraph\Block\FilePath;
use App\Domain\Project\Entity\Conclusion\Paragraph\Block\Id;
use App\Domain\Project\Entity\Conclusion\Paragraph\Block\Kind;
use DomainException;

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
   /**
    * @var BlockFilePathResolverInterface
    */
   private BlockFilePathResolverInterface $pathResolver;

   public function __construct(BlockRepository $blocks, Flusher $flusher, BlockFilePathResolverInterface $pathResolver)
   {
      $this->blocks = $blocks;
      $this->flusher = $flusher;
      $this->pathResolver = $pathResolver;
   }

   public function handle(DTO $dto): Block
   {
      $block = $this->blocks->get(new Id($dto->block_id));

      if ($block->getKind()->getValue() === $dto->kind) {
         return $block;
      }

      switch ($dto->kind) {
         case KIND::TEXT:
            $block->setTextKind();
            if (!$block->getFilePath() instanceof FilePath) {
               $block->setFilePath($this->pathResolver->resolve($block));
            }
            break;
         case Kind::DICT:
            $block->setDictKind();
            break;
         default:
            throw new DomainException("Trying to set wrong kind of block: {$dto->kind}");
      }

      $this->blocks->add($block);
      $this->flusher->flush();

      return $block;
   }
}
