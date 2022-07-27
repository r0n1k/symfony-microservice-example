<?php


namespace App\Domain\Project\UseCase\Project\Upsert;

use OpenApi\Annotations as OA;

/**
 * Class DictionaryDTO
 * @OA\Schema(schema="UpsertProjectDTO-Dictionary")
 */
class DictionaryDTO
{

   /**
    * @var array
    */
   public array $declarant = [];

   public array $customer = [];

   public array $developer = [];

   public array $build_object = [];

   public array $expertise_organization = [];

}
