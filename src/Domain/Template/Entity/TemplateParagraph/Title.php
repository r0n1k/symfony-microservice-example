<?php


namespace App\Domain\Template\Entity\TemplateParagraph;


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

   public static function of(string $title)
   {
      return new self($title);
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
