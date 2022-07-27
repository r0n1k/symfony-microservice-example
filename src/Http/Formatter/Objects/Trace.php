<?php


namespace App\Http\Formatter\Objects;


class Trace
{

   private $stacktrace;

   public function __construct($stacktrace)
   {
      $this->stacktrace = $stacktrace;
   }

   public function getStacktrace()
   {
      return $this->stacktrace;
   }

}
