<?php


namespace App\Domain\Project\Entity\Users\User;


use Webmozart\Assert\Assert;

class FullName
{
   /**
    * @var string
    */
   protected string $fullName;

   public function __construct(string $fullName)
   {
      Assert::notEmpty($fullName);
      $this->fullName = $fullName;
   }

   public function getValue(): string
   {
      return $this->fullName;
   }

   public function __toString()
   {
      return $this->fullName;
   }
}
