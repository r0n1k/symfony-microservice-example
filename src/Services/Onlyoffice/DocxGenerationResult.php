<?php


namespace App\Services\Onlyoffice;


class DocxGenerationResult
{

   public function __construct($savedPath, $key)
   {
      $this->savedPath = $savedPath;
      $this->key = $key;
   }

   public ?string $savedPath;

   public ?string $key;

}
