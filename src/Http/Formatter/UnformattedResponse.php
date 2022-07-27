<?php


namespace App\Http\Formatter;

class UnformattedResponse
{

   private $data;
   /**
    * @var int
    */
   private $statusCode;
   /**
    * @var array
    */
   private $errors;

   public function __construct($data, $statusCode = 200, $errors = [])
   {
      $this->data = $data;
      $this->statusCode = $statusCode;
      $this->errors = $errors;
   }

   public function getData()
   {
      return $this->data;
   }

   public function getStatusCode()
   {
      return $this->statusCode;
   }

   public function getErrors()
   {
      return $this->errors;
   }
}
