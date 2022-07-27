<?php


namespace App\Domain\Project\Entity\Users\User\Certificate;


class Id
{
   /**
    * @var int
    */
   private int $value;

   public function __construct(int $id)
   {
      $this->value = $id;
   }

   public function getValue(): int
   {
      return $this->value;
   }

   public function __toString()
   {
      return (string)$this->value;
   }
}
