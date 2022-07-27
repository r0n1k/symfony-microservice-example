<?php


namespace App\Domain\Project\UseCase\Conclusion\Paragraph\Create;

use OpenApi\Annotations as OA;

/**
 * Class DTO
 * @package App\Domain\UseCase\Conclusion\Paragraph\Create
 *
 * @OA\Schema(schema="CreateConclusionParagraphDTO", required={"title", "order"})
 */
class DTO
{

   /**
    * @var string
    */
   public $conclusion_id;

   /**
    * @var int|null
    * @OA\Property(description="ID родителя", nullable=true, type="integer")
    */
   public $parent_id;

   /**
    * @var string
    * @OA\Property(description="Название")
    */
   public $title;

    /**
     * @var int
     * @OA\Property(description="Порядковый номер")
     */
    public $order;
}
