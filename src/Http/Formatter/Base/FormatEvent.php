<?php


namespace App\Http\Formatter\Base;

abstract class FormatEvent
{

   /**
    * @var bool
    */
   private bool $formatted = false;

   /**
    * @var mixed
    */
   private $formattedData;
   /**
    * @var mixed
    */
   private $formattableData;

   public function __construct($formattableData)
   {
      $this->formattableData = $formattableData;
   }

   public function isFormatted()
   {
      return $this->formatted;
   }

   public function markFormatted()
   {
      $this->formatted = true;
   }


   public function setFormattedData($data)
   {
      $this->formattedData = $data;
   }

   public function getFormattedData()
   {
      return $this->formattedData;
   }

   public function getFormattableData() {
      return $this->formattableData;
   }
}
