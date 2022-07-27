<?php


namespace App\Domain\Project\UseCase\Conclusion\SetFileTypeState;

use Symfony\Component\Validator\Constraints as Assert;
use OpenApi\Annotations as OA;


class DTO
{
    /**
     * @Assert\Uuid()
     */
    public string $conclusion_id;

    /**
     * @OA\Property(
     *     type="string",
     *     nullable=true,
     *     @OA\Schema(ref="#/components/schemas/ConclusionFileTypeState"),
     * )
     */
    public ?string $file_type_state;
}
