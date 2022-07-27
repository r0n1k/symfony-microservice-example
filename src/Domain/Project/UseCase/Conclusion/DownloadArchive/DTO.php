<?php


namespace App\Domain\Project\UseCase\Conclusion\DownloadArchive;

use OpenApi\Annotations as OA;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * Class DTO
 * @package App\Domain\UseCase\Conclusion\DownloadArchive
 *
 * @OA\Schema(schema="ConclusionDownloadArchiveDTO")
 */
class DTO
{
   /**
    * @Assert\Uuid()
    * @var string
    */
   public string $conclusion_id;

    /**
     * @var int
     */
   public int $pdf_id;

}
