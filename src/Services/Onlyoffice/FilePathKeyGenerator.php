<?php


namespace App\Services\Onlyoffice;


use App\Services\SiteEnvResolver;
use Ramsey\Uuid\Uuid;

class FilePathKeyGenerator
{

   /**
    * @var string|null
    */
   private ?string $host;

   public function __construct(SiteEnvResolver $resolver)
   {
      $this->host = $resolver->resolve();
   }

   public function generate()
   {
      return $this->host . ':' . Uuid::uuid4();
   }
}
