<?php


namespace App\Domain\Project\UseCase\Conclusion\Paragraph\Block\Create;

use OpenApi\Annotations as OA;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * Class DTO
 * @package App\Domain\UseCase\Conclusion\Paragraph\Block\Create
 *
 * @OA\Schema(schema="CreateConclusionBlockDTO")
 */
class DTO
{

   /**
    * @var int
    * @Assert\NotBlank()
    */
   public int $paragraph_id;

   /**
    * @var string
    * @Assert\Choice(choices={
    *    "dict",
    *    "text",
    * })
    * @OA\Property(ref="#/components/schemas/ConclusionBlockKind")
    */
   public string $kind;

    /**
     * @var int
     * @Assert\NotBlank()
     * @OA\Property(description="Порядковый номер")
     */
    public int $order;
}
