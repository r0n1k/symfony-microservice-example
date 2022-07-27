<?php

namespace App\Domain\Project\UseCase\Conclusion\Paragraph\Block\Resort;

use App\Domain\Common\Flusher;
use App\Domain\Project\Entity\Conclusion\Paragraph\Block\Order;
use App\Domain\Project\Entity\Conclusion\Paragraph\Id as ParagraphId;
use App\Domain\Project\Repository\Conclusion\Paragraph\Block\BlockRepository;
use App\Domain\Project\Entity\Conclusion\Paragraph\Block\Id;
use App\Domain\Project\Repository\Conclusion\Paragraph\ParagraphRepository;

class Handler
{
    private BlockRepository $blocks;
    private Flusher $flusher;
    private ParagraphRepository $paragraphs;

    public function __construct(
        BlockRepository $blocks,
        ParagraphRepository $paragraphs,
        Flusher $flusher
    )
    {
        $this->blocks = $blocks;
        $this->flusher = $flusher;
        $this->paragraphs = $paragraphs;
    }

    public function handle(DTO $dto)
    {
        foreach ($dto->blocks as $sortingBlock) {
            $block = $this->blocks->get(new Id($sortingBlock['block_id']));
            $paragraph = $this->paragraphs->get(new ParagraphId($sortingBlock['paragraph_id']));

            if ($block->getParagraph()->getId()->getValue() !== $paragraph->getId()->getValue()) {
                $block->setParagraph($paragraph);
            }

            $block->setOrder(new Order($sortingBlock['order']));

            $this->blocks->add($block);
        }

        $this->flusher->flush();
    }
}
