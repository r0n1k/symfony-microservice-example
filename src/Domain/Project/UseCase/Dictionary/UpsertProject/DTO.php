<?php


namespace App\Domain\Project\UseCase\Dictionary\UpsertProject;


use Symfony\Component\Validator\Constraints as Assert;
use OpenApi\Annotations as OA;

/**
 * Class DTO
 * @package App\Domain\Project\UseCase\Dictionary\UpsertProject
 *
 * @OA\Schema(schema="UpsertDictionaryDTO")
 */
class DTO
{

   /**
    * @var string
    * @Assert\NotBlank()
    * @Assert\Uuid()
    */
   public string $project_id;

   /**
    * @var string
    * @Assert\NotBlank()
    */
   public string $dictionary_key;

   /**
    * @OA\Property(type="string")
    * @var string
    * @Assert\NotBlank()
    */
   public string $value;
}
