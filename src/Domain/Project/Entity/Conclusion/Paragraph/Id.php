<?php


namespace App\Domain\Project\Entity\Conclusion\Paragraph;


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

    public static function of($id)
    {
       return new self($id);
    }

    public function getValue(): int
   {
      return $this->value;
   }

   public function __toString()
   {
      return (string)$this->value;
   }

   public function isEqual(self $anotherId)
   {
      return $anotherId->value === $this->value;
   }

}
