<?php


namespace App\Domain\Project\UseCase\Conclusion\Delete;


use Symfony\Component\Validator\Constraints as Assert;

class DTO
{

   /**
    * @Assert\Uuid()
    * @var string
    */
   public string $conclusion_id;

}
