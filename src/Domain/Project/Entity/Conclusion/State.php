<?php


namespace App\Domain\Project\Entity\Conclusion;


use Webmozart\Assert\Assert;

class State
{
   public const DEFAULT = 'default';
   public const REMOVED = 'removed';
   public const LOCKED = 'locked';

   protected string $state;

   public function __construct(string $state)
   {
      Assert::oneOf($state, [
         self::DEFAULT,
         self::REMOVED,
         self::LOCKED,
      ]);

      $this->state = $state;
   }

   public static function default() {
      return new self(self::DEFAULT);
   }

   public static function removed() {
      return new self(self::REMOVED);
   }

   public static function locked() {
      return new self(self::LOCKED);
   }

   public function getValue() {
      return $this->state;
   }

   public function __toString()
   {
      return $this->state;
   }

    public function isLocked()
    {
       return $this->state === self::LOCKED;
    }

   public function isRemoved()
   {
      return $this->state === self::REMOVED;
   }

}
