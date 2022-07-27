<?php


namespace App\Services\DomainEvents;


use App\Domain\Common\DomainEvent;
use App\Domain\Common\DomainEventDispatcher;
use App\Domain\Common\DomainEventSubscriber;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpKernel\KernelEvents;

/** @noinspection PhpUnused */

class AppDomainEventDispatcher implements DomainEventSubscriber, EventSubscriberInterface
{
   /**
    * @var EventDispatcherInterface
    */
   private EventDispatcherInterface $dispatcher;

   public function __construct(EventDispatcherInterface $dispatcher)
   {
      $this->dispatcher = $dispatcher;
   }

   public function handle(DomainEvent $event): void
   {
      $this->dispatcher->dispatch($event, Events::DOMAIN_EVENT);
   }

   /**
    * @inheritDoc
    */
   public static function getSubscribedEvents()
   {
      return [
         KernelEvents::REQUEST => 'subscribe',
      ];
   }

   public function subscribe()
   {
      DomainEventDispatcher::instance()->subscribe($this);
   }

   public function addDomainSubscribers($subscribers)
   {
      foreach ($subscribers as $i => $subscriber) {
         DomainEventDispatcher::instance()->subscribe($subscriber);
      }
   }
}
