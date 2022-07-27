<?php


namespace App\Domain\Project\UseCase\Conclusion\Paragraph\Block\SetExecutor;

use OpenApi\Annotations as OA;

/**
 * @OA\Schema(schema="SetBlockExecutorDTO")
 */
class DTO
{

   /**
    * @var int
    */
   public int $block_id;

   /**
    * @var int|null
    * @OA\Property()
    */
   public ?int $user_id;
}
