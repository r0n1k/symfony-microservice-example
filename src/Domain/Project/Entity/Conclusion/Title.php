<?php


namespace App\Domain\Project\Entity\Conclusion;


use Webmozart\Assert\Assert;

class Title
{
   /**
    * @var string uuid
    */
   private string $value;

   public function __construct($title)
   {
      Assert::notEmpty($title);
      $this->value = $title;
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
