<?php


namespace App\Domain\Project\Entity\Conclusion;


use Webmozart\Assert\Assert;

class TemplateId
{
   /**
    * @var string uuid
    */
   private string $value;

   public function __construct($uuid)
   {
      Assert::notEmpty($uuid);
      Assert::uuid($uuid);
      $this->value = $uuid;
   }

   /**
    * @return string uuid
    */
   public function getValue(): string
   {
      return $this->value;
   }

   public function __toString()
   {
      return $this->value;
   }
}
