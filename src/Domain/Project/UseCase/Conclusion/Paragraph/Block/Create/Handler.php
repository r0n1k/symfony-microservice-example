<?php


namespace App\Domain\Project\UseCase\Conclusion\Paragraph\Block\Create;

use App\Domain\Common\Flusher;
use App\Domain\Common\Service\BlockFilePathResolverInterface;
use App\Domain\Project\Entity\Conclusion\Paragraph\Block\Block;
use App\Domain\Project\Entity\Conclusion\Paragraph\Block\Order;
use App\Domain\Project\Repository\Conclusion\Paragraph\Block\BlockRepository;
use App\Domain\Project\Entity\Conclusion\Paragraph\Block\Kind;
use App\Domain\Project\Entity\Conclusion\Paragraph\Block\State;
use App\Domain\Project\Entity\Conclusion\Paragraph\Id as ParagraphId;
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
   /**
    * @var BlockRepository
    */
   private BlockRepository $blocks;
   /**
    * @var BlockFilePathResolverInterface
    */
   private BlockFilePathResolverInterface $pathResolver;

   public function __construct(ParagraphRepository $paragraphs,
                               BlockRepository $blocks,
                               BlockFilePathResolverInterface $pathResolver,
                               Flusher $flusher)
   {
      $this->paragraphs = $paragraphs;
      $this->flusher = $flusher;
      $this->blocks = $blocks;
      $this->pathResolver = $pathResolver;
   }

   public function handle(DTO $dto): Block
   {
      $paragraph = $this->paragraphs->get(new ParagraphId($dto->paragraph_id));

      $id = $this->blocks->nextId();
      $kind = new Kind($dto->kind);
      $state = new State(State::WAITING_TO_START);
      $order = new Order($dto->order);
      $block = $paragraph->addBlock($id, $kind, $state, $order);

      if ($block->getKind()->isText()) {
         $path = $this->pathResolver->resolve($block);
         $block->setFilePath($path);
      }

      $this->flusher->flush();

      return $block;
   }
}
