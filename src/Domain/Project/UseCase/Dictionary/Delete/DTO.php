<?php


namespace App\Domain\Project\UseCase\Dictionary\Delete;


class DTO
{

   public string $project_id;

   public ?int $block_id = null;

   public string $dictionary_key;

}
