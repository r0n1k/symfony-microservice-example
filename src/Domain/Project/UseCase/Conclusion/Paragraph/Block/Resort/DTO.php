<?php

namespace App\Domain\Project\UseCase\Conclusion\Paragraph\Block\Resort;

use OpenApi\Annotations as OA;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * Class DTO
 *
 * @package App\Domain\UseCase\Conclusion\Paragraph\Block\Resort
 * @OA\Schema(schema="ResortConclusionBlockDTO")
 */
class DTO
{

    /**
     * @var array
     * @OA\Property(description="Массив блоков для сортировки")
     *
     * @Assert\All({
     *   @Assert\Collection(
     *     fields = {
     *       "block_id" = @Assert\Positive,
     *       "paragraph_id" = @Assert\Positive,
     *       "order" = @Assert\PositiveOrZero,
     *     }
     *   )
     * })
     */
    public array $blocks;
}
