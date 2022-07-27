<?php


namespace App\Domain\Project\UseCase\Conclusion\Paragraph\Block\SetKind;

use OpenApi\Annotations as OA;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * Class DTO
 * @package App\Domain\UseCase\Conclusion\Paragraph\Block\SetKind
 *
 * @OA\Schema(schema="ConclusionBlockSetKindDTO")
 */
class DTO
{

   public $block_id;

   /**
    * @var string
    * @Assert\Choice(choices={"text", "dict"})
    * @Assert\NotBlank()
    * @OA\Property(ref="#/components/schemas/ConclusionBlockKind")
    */
   public $kind;
}
