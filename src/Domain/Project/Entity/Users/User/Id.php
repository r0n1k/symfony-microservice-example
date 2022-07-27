<?php


namespace App\Domain\Project\Entity\Users\User;



use InvalidArgumentException;

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
       /** @noinspection TypeUnsafeComparisonInspection */
       if ((int)$id != $id) {
          throw new InvalidArgumentException('Wrong user id');
       }
       return new self((int)$id);
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
