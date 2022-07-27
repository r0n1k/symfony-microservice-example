<?php


namespace App\Domain\Project\UseCase\Conclusion\Rename;

use OpenApi\Annotations as OA;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * Class DTO
 * @package App\Domain\UseCase\Conclusion\Rename
 *
 * @OA\Schema(schema="ConclusionRenameDTO")
 */
class DTO
{

   /**
    * @Assert\NotBlank()
    * @OA\Property(type="string", property="name")
    * @var string
    */
   public string $name;

   /**
    * @Assert\Uuid()
    * @var string
    */
   public string $conclusion_id;

}
