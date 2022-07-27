<?php


namespace App\Services;


class SiteEnvResolver
{

   /**
    * @var string|null
    */
   private ?string $instanceName;

   public function __construct(string $instanceName)
   {
      $this->instanceName = !empty($instanceName) ? $instanceName : null;
   }

   public function resolve(): ?string
   {
      return $this->instanceName;
   }

}
