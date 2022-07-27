<?php


namespace App\Tests\Helper\MockClasses;


use App\Domain\Project\UseCase\Project\Upsert\DTO;
use App\Services\Project\ProjectFetcherInterface;

class TestProjectFetcher implements ProjectFetcherInterface
{

   public function fetch(string $project_id): DTO
   {
      $dto = new DTO();
      $dto->project_id = $project_id;
      return $dto;
   }
}
