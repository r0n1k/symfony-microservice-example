<?php


namespace App\Domain\Project\Entity\Conclusion\Paragraph;


class Title
{

   /**
    * @var string
    */
   private string $title;

   public function __construct(string $title)
   {
      $this->title = $title;
   }

    public static function of(string $string)
    {
       return new self($string);
    }

    public function getValue(): string
   {
      return $this->title;
   }

   public function __toString()
   {
      return $this->title;
   }

}
