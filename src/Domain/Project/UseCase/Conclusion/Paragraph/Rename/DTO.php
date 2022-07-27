<?php


namespace App\Domain\Project\UseCase\Conclusion\Paragraph\Rename;

use OpenApi\Annotations as OA;

/**
 * Class DTO
 * @package App\Domain\UseCase\Conclusion\Paragraph\Edit
 *
 * @OA\Schema(schema="RenameParagraphDTO")
 */
class DTO
{

   /**
    * @var int
    */
   public int $paragraph_id;

   /**
    * @OA\Property()
    * @var string
    */
   public string $title;
}
