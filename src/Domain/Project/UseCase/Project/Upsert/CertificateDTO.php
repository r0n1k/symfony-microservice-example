<?php


namespace App\Domain\Project\UseCase\Project\Upsert;

use OpenApi\Annotations as OA;

/**
 * Class CertificateDTO
 * @package App\Domain\UseCase\Project\Upsert
 *
 * @OA\Schema(schema="UpsertProjectDTO-Certificate")
 */
class CertificateDTO
{

   /**
    * @OA\Property()
    * @var string
    */
   public string $scope;

}
