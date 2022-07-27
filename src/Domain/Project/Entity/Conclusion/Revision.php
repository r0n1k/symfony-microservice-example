<?php


namespace App\Domain\Project\Entity\Conclusion;

use Webmozart\Assert\Assert;

class Revision
{

   /** @var int */
   protected int $revision;

   public function __construct(int $revision)
   {
      Assert::greaterThanEq($revision, 0);
      $this->revision = $revision;
   }

   public function getValue(): int
   {
      return $this->revision;
   }

   public function next(): Revision
   {
      return new Revision($this->revision + 1);
   }

   public function isFirst()
   {
      return $this->getValue() === 1;
   }

   public function prev()
   {
      Assert::greaterThan($this->revision, 1);
      return new Revision($this->revision - 1);
   }

   public function __toString()
   {
      return (string)$this->revision;
   }
}
