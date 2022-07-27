<?php


namespace App\Http\Formatter\Formatters\Conclusion\Pdf;

use App\Domain\Project\Entity\Conclusion\Pdf\Pdf;
use App\Domain\Project\Entity\Conclusion\Pdf\Signature\Signature;
use App\Http\Formatter\Base\EntityFormatter;
use App\Http\Formatter\Base\FormatEvent;
use App\Services\EntityLogger\Repository\EntityLogRepository;
use Doctrine\ORM\EntityManagerInterface;
use OpenApi\Annotations as OA;

/**
 * @noinspection PhpUnused
 */
class SignatureFormatter extends EntityFormatter
{

    private EntityManagerInterface $entityManager;
    private EntityLogRepository $logRepository;

    public function __construct(EntityManagerInterface $entityManager, EntityLogRepository $logRepository)
    {
        $this->entityManager = $entityManager;
        $this->logRepository = $logRepository;
    }

    /**
     * @param Signature $sig
     * @return array
     */
    public function format($sig)
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
            'id' => $sig->getId(),
            'path' => $sig->getPath(),
            'data' => $sig->getData()
            //'logs' => $this->logRepository->findAllForEntity($paragraph)
        ];
    }

    /**
     * @inheritDoc
     */
    protected function supports(FormatEvent $event): bool
    {
        return $event->getFormattableData() instanceof Signature;
    }
}
