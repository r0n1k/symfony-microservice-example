<?php


namespace App\Domain\Project\UseCase\Conclusion\Create;

use App\Domain\Project\Entity\Users\User\User;
use Symfony\Component\Validator\Constraints as Assert;
use OpenApi\Annotations as OA;

/**
 * Class DTO
 * @OA\Schema(schema="CreateConclusionDTO")
 */
class DTO
{

   /**
    * @var string
    * @Assert\NotBlank
    * @Assert\Uuid
    */
   public string $project_id;

   /**
    * @var string
    * @Assert\Choice(
    *    choices={
    *       "generator", "files", "online",
    *    }
    * )
    * @OA\Property(type="string",
    *    default="generator",
    *    nullable=true,
    *    @OA\Schema(ref="#/components/schemas/ConclusionKind"),
    * )
    */
   public string $kind = 'generator';

   /**
    * @Assert\Uuid()
    * @OA\Property(type="string", format="uuid", description="ID шаблона", nullable=true),
    * @var string|null
    */
   public ?string $template_id = null;

    /**
     * @var int
     */
   public int $author_id;

   /**
    * @OA\Property(type="string")
    * @Assert\NotBlank()
    * @var string
    */
   public string $name;

   public bool $is_local;
}
