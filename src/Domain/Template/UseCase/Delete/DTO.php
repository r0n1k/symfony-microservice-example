<?php


namespace App\Domain\Template\UseCase\Delete;


use Symfony\Component\Validator\Constraints as Assert;

class DTO
{

   /**
    * @Assert\Uuid()
    * @Assert\NotBlank()
    * @var string
    */
   public $template_id;

}
