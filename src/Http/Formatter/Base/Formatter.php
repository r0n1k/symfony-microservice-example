<?php


namespace App\Http\Formatter\Base;

use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Contracts\EventDispatcher\EventDispatcherInterface;

abstract class Formatter implements EventSubscriberInterface
{

   protected static function events() {
      return [];
   }

   public static function getSubscribedEvents()
   {
      $result = [];
      foreach (static::events() as $eventName) {
         $result[$eventName] = 'handle';
      }
      return $result;
   }

   public function handle(FormatEvent $event)
   {
      if ($event->isFormatted() || !$this->supports($event)) {
         return;
      }

      $event->setFormattedData($this->format($event->getFormattableData()));
      $event->markFormatted();
   }


   /**
    * @param $formattableData
    * @return mixed
    */
   abstract public function format($formattableData);

   /**
    * Checks if formatter can handle the event
    * @param FormatEvent $event
    * @return bool
    */
   abstract protected function supports(FormatEvent $event): bool;
}
