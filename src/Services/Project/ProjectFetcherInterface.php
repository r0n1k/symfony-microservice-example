<?php


namespace App\Services\Project;


use App\Domain\Project\UseCase\Project\Upsert\DTO;

interface ProjectFetcherInterface
{

   public function fetch(string $project_id): DTO;
}
