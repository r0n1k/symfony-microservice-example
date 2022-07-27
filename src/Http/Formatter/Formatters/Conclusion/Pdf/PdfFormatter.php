<?php


namespace App\Http\Formatter\Formatters\Conclusion\Pdf;

use App\Domain\Project\Entity\Conclusion\Pdf\Pdf;
use App\Http\Formatter\Base\EntityFormatter;
use App\Http\Formatter\Base\FormatEvent;
use App\Services\EntityLogger\Repository\EntityLogRepository;
use Doctrine\ORM\EntityManagerInterface;
use OpenApi\Annotations as OA;

/**
 * @noinspection PhpUnused
 */
class PdfFormatter extends EntityFormatter
{

    private EntityManagerInterface $entityManager;
    private EntityLogRepository $logRepository;

    public function __construct(EntityManagerInterface $entityManager, EntityLogRepository $logRepository)
    {
        $this->entityManager = $entityManager;
        $this->logRepository = $logRepository;
    }

    /**
     * @param Pdf $pdf
     * @return array
     */
    public function format($pdf)
    {

        /**
         * @OA\Schema(schema="ConclusionPdf", type="object",
         *    @OA\Property(property="id", type="integer"),
         *    @OA\Property(property="conclusion_id", type="string", format="uuid"),
         *    @OA\Property(property="path", type="string"),
         *    @OA\Property(property="created_at", type="int"),
         * )
         */
        return [
            'id' => $pdf->getId(),
            'conclusion_id' => (string)$pdf->getConclusion()->getId(),
            'path' => $pdf->getPath(),
            'created_at' => $pdf->getCreatedAt() ? $pdf->getCreatedAt()->getTimestamp() : null,
            'state' => $pdf->getState(),
            'file_name' => $pdf->getFileName(),
            'signatures' => $pdf->getSignatures(),
            //'logs' => $this->logRepository->findAllForEntity($paragraph)
        ];
    }

    /**
     * @inheritDoc
     */
    protected function supports(FormatEvent $event): bool
    {
        return $event->getFormattableData() instanceof Pdf;
    }
}
