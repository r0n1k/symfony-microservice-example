<?php


namespace App\Domain\Project\UseCase\Conclusion\Paragraph\Block\SetState;

use OpenApi\Annotations as OA;
use Symfony\Component\Validator\Constraints as Assert;


/**
 * Class DTO
 *
 * @package App\Domain\UseCase\Conclusion\Paragraph\Block\SetState
 * @OA\Schema(schema="ChangeConclusionBlockStateDTO")
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

    /**
     * @OA\Property(ref="#/components/schemas/ConclusionBlockState")
     * @Assert\Choice({
     *    "waiting_to_start",
     *    "work_in_progress",
     *    "sent_to_review",
     *    "on_review",
     *    "declined",
     *    "completed"
     * })
     */
    public ?string $new_state = null;

    /**
     * @OA\Property(description="Причина отклонения")
     */
    public ?string $decline_reason = null;
}
