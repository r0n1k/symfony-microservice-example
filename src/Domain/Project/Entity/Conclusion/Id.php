<?php


namespace App\Domain\Project\Entity\Conclusion;


use Ramsey\Uuid\Uuid;
use Webmozart\Assert\Assert;

class Id
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

   public static function next(): self
   {
      return new self(Uuid::uuid4()->toString());
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

   public function isEqual(Id $anotherId)
   {
      return $anotherId->value === $this->value;
   }
}

