<?php


namespace App\Domain\Project\UseCase\Project\Upsert;

use OpenApi\Annotations as OA;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * Class ProjectDTO
 * @package App\Domain\UseCase\Project\Upsert
 *
 * @OA\Schema(schema="UpsertProjectDTO")
 */
class DTO
{

   /**
    * @OA\Property(type="string", format="uuid")
    * @var string uuid
    * @Assert\Uuid()
    * @Assert\NotBlank()
    */
   public string $project_id;

   /**
    * @OA\Property()
    * @Assert\NotBlank()
    * @var string
    */
   public string $project_name;

   /**
    * @OA\Property()
    * @Assert\NotBlank()
    * @var string
    */
   public string $project_state;

   /**
    * @var UserDTO[]
    * @OA\Property(type="array", @OA\Items(ref="#/components/schemas/UpsertProjectDTO-User"))
    */
   public array $users;

   /**
    * @Assert\NotNull()
    * @var DictionaryDTO
    * @OA\Property(ref="#/components/schemas/UpsertProjectDTO-Dictionary")
    */
   public DictionaryDTO $dictionaries;

   /**
    * @var string[]
    */
   public array $certificates = [];
}
