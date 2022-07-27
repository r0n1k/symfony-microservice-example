<?php


namespace App\Domain\Project\Entity\Project;

use Webmozart\Assert\Assert;
use OpenApi\Annotations as OA;

class Name
{
   /**
    * @OA\Schema(schema="ProjectName", type="text")
    * @var string
    */
   private string $value;

   public function getValue(): string
   {
      return $this->value;
   }

   public function __construct(string $name)
   {
      Assert::notEmpty($name);
      $this->value = $name;
   }

   public function __toString()
   {
      return $this->value;
   }
}
