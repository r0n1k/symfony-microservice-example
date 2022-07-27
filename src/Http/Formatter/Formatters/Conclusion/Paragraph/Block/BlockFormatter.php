<?php


namespace App\Http\Formatter\Formatters\Conclusion\Paragraph\Block;

use App\Domain\Project\Entity\Conclusion\Paragraph\Block\Block;
use App\Http\Formatter\Base\EntityFormatter;
use App\Http\Formatter\Base\FormatEvent;
use App\Http\ReadModel\BlockDictionariesFetcher;
use App\Services\EntityLogger\Repository\EntityLogRepository;
use OpenApi\Annotations as OA;

/**
 * @noinspection PhpUnused
 */

class BlockFormatter extends EntityFormatter
{

    /**
     * @var BlockDictionariesFetcher
     */
    private BlockDictionariesFetcher $dictionaries;
    private EntityLogRepository $logRepository;

    public function __construct(BlockDictionariesFetcher $dictionaries, EntityLogRepository $logRepository)
    {
        $this->dictionaries = $dictionaries;
        $this->logRepository = $logRepository;
    }

    /**
     * @param Block $block
     * @return array
     */
    public function format($block)
    {
        /**
         * @OA\Schema(schema="ConclusionBlock", type="object",
         *    @OA\Property(property="id", type="integer"),
         *    @OA\Property(property="paragraph_id", type="integer"),
         *    @OA\Property(property="conclusion_id", type="string", format="uuid"),
         *    @OA\Property(property="kind", ref="#/components/schemas/ConclusionBlockKind"),
         *    @OA\Property(property="executor", ref="#/components/schemas/User"),
         *    @OA\Property(property="file_path", type="string", nullable=true),
         *    @OA\Property(property="html", type="string", nullable=true, description="HTML превью документа"),
         *    @OA\Property(property="preview_html", type="string", nullable=true, description="HTML превью документа"),
         * )
         * @OA\Property(property="decline_reason", type="string", nullable=true, description="Причина отклонения блока"),
         */
        $result = [
            'id' => $block->getId()->getValue(),
            'paragraph_id' => $block->getParagraph()->getId()->getValue(),
            'conclusion_id' => $block->getParagraph()->getConclusion()->getId()->getValue(),
            'kind' => (string)$block->getKind(),
            'executor' => $block->getExecutor(),
            'state' => (string)$block->getState(),
            'sort_order' => $block->getOrder()->getValue(),
            'decline_reason' => $block->getDeclineReason(),
            'logs' => $this->logRepository->findAllForEntity($block),
        ];

        if ($block->getKind()->isText()) {
            $result['file_path'] = (string)$block->getFilePath();
            if ($block->getFilePath()) {
                $result['file_path_key'] = $block->getFilePath()->getKey();
            }
            $result['html'] = $block->getHtml() ?: '';
            $result['preview_html'] = $block->getPreviewHtml() ?: '';
        } else {
            $result['dictionaries'] = $this->dictionaries->fetch($block);
            $result['custom_values'] = array_values($block->getCustomValues()->toArray());
        }

        return $result;
    }

    /**
     * @inheritDoc
     */
    protected function supports(FormatEvent $event): bool
    {
        return $event->getFormattableData() instanceof Block;
    }
}
