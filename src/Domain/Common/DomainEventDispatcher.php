<?php


namespace App\Domain\Common;


class DomainEventDispatcher
{
   /**
    * @var DomainEventDispatcher
    */
   private static ?DomainEventDispatcher $instance = null;

   /**
    * @var DomainEventSubscriber[]
    */
   private array $subscribers = [];

   private array $transaction = [];

   private function __construct()
   {
   }

   public function clear()
   {
      $this->transaction = [];
   }

   private function __clone()
   {
   }

   public static function instance(): self
   {
      if (!self::$instance) {
         self::$instance = new self;
      }

      return self::$instance;
   }

   public function dispatch(DomainEvent $event)
   {
      foreach ($this->transaction as $i => $existentEvent) {
         if (get_class($event) === get_class($existentEvent) && $event->getEntity() === $existentEvent->getEntity()) {
            $this->transaction[$i] = $event;
            return;
         }
      }
      $this->transaction[] = $event;
   }

   public function flush()
   {
      while ($event = array_shift($this->transaction)) {
         foreach ($this->subscribers as $subscriber) {
            $subscriber->handle($event);
         }
      }
   }

   public function subscribe(DomainEventSubscriber $subscriber)
   {
      $this->subscribers[] = $subscriber;
   }

}
