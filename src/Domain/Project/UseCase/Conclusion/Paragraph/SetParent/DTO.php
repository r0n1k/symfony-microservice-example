<?php


namespace App\Domain\Project\UseCase\Conclusion\Paragraph\SetParent;

use OpenApi\Annotations as OA;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * Class DTO
 * @package App\Domain\UseCase\Conclusion\Paragraph\SetParent
 *
 * @OA\Schema(schema="SetParagraphsParentDTO")
 */
class DTO
{

   /**
    * @Assert\NotBlank()
    * @var int
    */
   public int $paragraph_id;

   /**
    * @OA\Property()
    * @var int|null
    */
   public ?int $parent_id;

    /**
     * @Assert\NotBlank()
     * @var int
     */
   public int $order;
}
