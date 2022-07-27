<?php


namespace App\Domain\Project\UseCase\Project\Upsert;

use OpenApi\Annotations as OA;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * Class UserDTO
 * @package App\Domain\UseCase\Project\Upsert
 *
 * @OA\Schema(schema="UpsertProjectDTO-User")
 */
class UserDTO
{

   /**
    * @var int
    * @OA\Property(type="integer")
    */
   public int $id;

   /**
    * @var string
    * @OA\Property(ref="#/components/schemas/UserEmail")
    */
   public string $email;

   /**
    * @var string
    * @OA\Property(ref="#/components/schemas/UserRole")
    */
   public string $role;

   /**
    * @var string
    * @OA\Property(type="string")
    */
   public string $full_name;

   /**
    * @var CertificateDTO[]
    * @OA\Property(type="array", @OA\Items(ref="#/components/schemas/UpsertProjectDTO-Certificate"))
    */
   public $certificates;

   /**
    * @var string expert|main_expert
    * @OA\Property(type="string", enum={"expert", "main_expert"})
    * @Assert\NotBlank()
    * @Assert\Choice(choices={"expert", "main_expert"})
    */
   public string $assignment_type;
}
