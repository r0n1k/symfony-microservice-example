<?php


namespace App\Domain\Project\UseCase\Conclusion\Paragraph\Block\Delete;

use OpenApi\Annotations\Schema;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * Class DTO
 * @package App\Domain\UseCase\Conclusion\Paragraph\Block\Delete
 * @Schema(schema="DeleteBlocksDTO")
 */
class DTO
{

   /**
    * @var int[]
    * @Assert\All({
    *   @Assert\Type("integer")
    * })
    */
   public array $block_ids;
}
