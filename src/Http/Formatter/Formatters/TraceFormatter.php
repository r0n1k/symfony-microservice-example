<?php


namespace App\Http\Formatter\Formatters;

use App\Http\Formatter\Base\FormatEvent;
use App\Http\Formatter\Base\Formatter;
use App\Http\Formatter\Events\FormatterEvents;

/**
 * @noinspection PhpUnused
 */
class TraceFormatter extends Formatter
{
   /**
    * @inheritDoc
    */
   protected static function events()
   {
      return [
         FormatterEvents::FORMAT_TRACE,
      ];
   }

   /**
    * @inheritDoc
    */
   public function format($trace)
   {
      return array_map(
         static function ($trace) {
            unset($trace['args']);
            return $trace;
         },
         $trace
      );
   }

   /**
    * @inheritDoc
    */
   protected function supports(FormatEvent $event): bool
   {
      return is_array($event->getFormattableData());
   }
}
