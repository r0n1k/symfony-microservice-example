<?php


namespace App\Domain\Project\UseCase\Dictionary\CreateCustom;


use Symfony\Component\Validator\Constraints as Assert;

class DTO
{

   /**
    * @var string
    * @Assert\Uuid()
    */
   public string $project_id;

   public ?int $block_id = null;

   public ?string $name = null;

   /**
    * @var string
    * @Assert\NotBlank()
    */
   public string $key;

   public ?string $value = null;

}
