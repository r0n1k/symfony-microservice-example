<?php


namespace App\Domain\Project\Entity\Conclusion\Paragraph;


class Order
{
   /**
    * @var int
    */
   private int $order;

   public function __construct(int $order)
   {
      $this->order = $order;
   }

   public static function initial()
   {
      return new self(0);
   }

    public static function of(int $order)
    {
       return new self($order);
    }

    public function getValue(): int
   {
      return $this->order;
   }

   public function __toString()
   {
      return (string)$this->order;
   }

   public function equals(Order $order)
   {
      return $this->order === $order->order;
   }

   public function greaterThen(Order $order)
   {
      return $this->order > $order->order;
   }
}
