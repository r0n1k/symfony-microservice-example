<?php


namespace App\Domain\Template\UseCase\Rename;


use Symfony\Component\Validator\Constraints as Assert;
use OpenApi\Annotations as OA;

/**
 * Class DTO
 * @package App\Domain\UseCase\Template\Rename
 * @OA\Schema(schema="TemplateRenameDTO")
 */
class DTO
{

   /**
    * @Assert\Uuid()
    * @Assert\NotBlank()
    * @var string
    */
   public $template_id;

   /**
    * @OA\Property()
    * @Assert\NotBlank()
    * @var string
    */
   public $name;

}
