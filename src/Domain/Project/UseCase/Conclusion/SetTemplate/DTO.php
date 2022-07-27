<?php


namespace App\Domain\Project\UseCase\Conclusion\SetTemplate;


use Symfony\Component\Validator\Constraints as Assert;
use OpenApi\Annotations as OA;

/**
 * Class DTO
 * @package App\Domain\UseCase\Conclusion\SetTemplate
 * @OA\Schema(schema="ConclusionSetTemplateDTO")
 */
class DTO
{
   /**
    * @Assert\Uuid()
    * @var string uuid
    */
   public string $conclusion_id;

   /**
    * @OA\Property(type="string", format="uuid", description="ID шаблона")
    * @Assert\Uuid()
    * @var string uuid
    */
   public string $template_id;

}
