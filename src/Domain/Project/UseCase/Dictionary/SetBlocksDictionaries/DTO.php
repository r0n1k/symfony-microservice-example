<?php


namespace App\Domain\Project\UseCase\Dictionary\SetBlocksDictionaries;


use Symfony\Component\Validator\Constraints as Assert;
use OpenApi\Annotations as OA;

/**
 * Class DTO
 * @OA\Schema(schema="SetBlocksDictionariesDTO")
 */
class DTO
{

   /**
    * @var int
    * @Assert\NotBlank()
    */
   public int $block_id;

   /**
    * @var string[]
    * @OA\Property(property="keys", type="array", description="Ключи словаря", @OA\Items(type="string"))
    */
   public array $keys;

}
