<?php


namespace App\Domain\Project\UseCase\Conclusion\SetComment;

use Symfony\Component\Validator\Constraints as Assert;


class DTO
{
    /**
     * @Assert\Uuid()
     */
    public string $conclusion_id;

    public ?string $comment;
}
