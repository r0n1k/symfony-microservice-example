<?php


namespace App\Domain\Project\UseCase\Conclusion\AllowToClient;


use Symfony\Component\Validator\Constraints as Assert;

class DTO
{
    /**
     * @var string UUID
     * @Assert\NotBlank()
     * @Assert\Uuid()
     */
    public string $conclusion_id;

    /**
     * @var bool
     */
    public bool $is_accessible;
}
