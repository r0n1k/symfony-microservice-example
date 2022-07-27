<?php


namespace App\Domain\Project\UseCase\User\Upsert;

use Symfony\Component\Validator\Constraints as Assert;

class UserDTO
{

   /**
    * @var int
    * @Assert\NotBlank()
    */
   public int $id;

   /**
    * @Assert\NotBlank()
    * @var string
    */
   public string $full_name;

   /**
    * @Assert\NotBlank()
    * @Assert\Choice(choices={
    *    "client",
    *    "project_manager",
    *    "admin",
    *    "expert",
    *    "verifier",
    * })
    * @var string
    */
   public string $role;

   /**
    * @var CertificateDTO[]
    */
   public array $certificates;
}
