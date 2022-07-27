<?php

namespace App\Domain\Template\UseCase\CreateFromConclusion;


use Symfony\Component\Validator\Constraints as Assert;
use OpenApi\Annotations as OA;

/**
 * Class DTO
 * @package App\Domain\UseCase\Template\CreateFromConclusion
 * @OA\Schema(schema="CreateTemplateFromConclusionDTO")
 */
class DTO
{

   /**
    * @OA\Property(ref="#/components/schemas/ConclusionId")
    * @Assert\NotBlank()
    * @Assert\Uuid()
    * @var string
    */
   public $conclusion_id;

   /**
    * @OA\Property(type="string", description="Название шаблона")
    * @Assert\NotBlank()
    * @var string
    */
   public $name;

}
