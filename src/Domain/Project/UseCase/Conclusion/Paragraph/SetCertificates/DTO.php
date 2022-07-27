<?php


namespace App\Domain\Project\UseCase\Conclusion\Paragraph\SetCertificates;


use Symfony\Component\Validator\Constraints as Assert;

class DTO
{

   /**
    * @var string[]
    */
   public $scopes = [];


   /**
    * @Assert\NotBlank()
    * @var int
    */
   public $paragraph_id;

}
