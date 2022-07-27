<?php


namespace App\Http\Formatter\Formatters\Conclusion\Paragraph;

use App\Domain\Project\Entity\Conclusion\Paragraph\Paragraph;
use App\Http\Formatter\Base\EntityFormatter;
use App\Http\Formatter\Base\FormatEvent;
use App\Services\EntityLogger\Repository\EntityLogRepository;
use Doctrine\ORM\EntityManagerInterface;
use OpenApi\Annotations as OA;

/**
 * @noinspection PhpUnused
 */
class ParagraphFormatter extends EntityFormatter
{

    private EntityManagerInterface $entityManager;
    private EntityLogRepository $logRepository;

    public function __construct(EntityManagerInterface $entityManager, EntityLogRepository $logRepository)
    {
        $this->entityManager = $entityManager;
        $this->logRepository = $logRepository;
    }

    /**
     * @param Paragraph $paragraph
     * @return array
     */
    public function format($paragraph)
    {

        /**
         * @OA\Schema(schema="ConclusionParagraph", type="object",
         *    @OA\Property(property="id", type="integer"),
         *    @OA\Property(property="conclusion_id", type="string", format="uuid"),
         *    @OA\Property(property="title", type="string"),
         *    @OA\Property(property="parent_id", oneOf={
         *       @OA\Schema(type="integer"),
         *       @OA\Schema(type="null"),
         *    }),
         *    @OA\Property(property="blocks", type="array", @OA\Items(ref="#/components/schemas/ConclusionBlock")),
         *    @OA\Property(property="order", type="integer"),
         * )
         */
        return [
            'id' => $paragraph->getId()->getValue(),
            'conclusion_id' => (string)$paragraph->getConclusion()->getId(),
            'title' => $paragraph->getTitle()->getValue(),
            'blocks' => $paragraph->getBlocks(),
            'parent_id' => $paragraph->getParent() ? $paragraph->getParent()->getId()->getValue() : null,
            'certificates' => $paragraph->getCertificates(),
            'order' => $paragraph->getOrder(),
            'logs' => $this->logRepository->findAllForEntity($paragraph)
        ];
    }

    /**
     * @inheritDoc
     */
    protected function supports(FormatEvent $event): bool
    {
        return $event->getFormattableData() instanceof Paragraph;
    }
}
