<?php


namespace App\Domain\Project\Entity\Users\User\Certificate;

use Webmozart\Assert\Assert;

class Scope
{
   /**
    * @var string
    */
   protected string $value;

   public function __construct(string $scope)
   {
      Assert::notEmpty($scope);

      $this->value = $scope;
   }

    public static function of(string $scope)
    {
       return new self($scope);
    }

    public function getValue(): string
   {
      return $this->value;
   }

   public function __toString()
   {
      return $this->value;
   }
}
