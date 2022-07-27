<?php


namespace App\Http\Formatter\Base;

use App\Http\Formatter\Events\FormatterEvents;

abstract class EntityFormatter extends Formatter
{
   /**
    * @inheritDoc
    */
   public static function getSubscribedEvents()
   {
      return [FormatterEvents::FORMAT_ENTITY => 'handle'];
   }
}
