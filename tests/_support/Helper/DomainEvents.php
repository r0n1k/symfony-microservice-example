<?php

namespace App\Tests\Helper;

// here you can define custom actions
// all public methods declared in helper class will be available in $I

use App\Domain\Common\DomainEvent;
use App\Services\DomainEvents\Events;
use Codeception\Module\Symfony;
use Codeception\TestInterface;
use PHPUnit\Framework\Assert;
use Symfony\Component\EventDispatcher\EventDispatcher;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class DomainEvents extends \Codeception\Module
{

   /**
    * @var EventSubscriberInterface
    */
   private $subscriber;

   public function _before(TestInterface $test)
   {
      /** @var Symfony $symfony */
      $symfony = $this->getModule('Symfony');

      /** @var EventDispatcher $dispatcher */
      $dispatcher = $symfony->grabService(EventDispatcherInterface::class);
      $dispatcher->addSubscriber($subscriber = new class implements EventSubscriberInterface {

         public array $recordedEvents = [];

         public static function getSubscribedEvents()
         {
            return [Events::DOMAIN_EVENT => 'handle'];
         }

         public function handle(DomainEvent $event)
         {
            $this->recordedEvents[] = $event;
         }
      });
      $this->subscriber = $subscriber;
      $subscriber->recordedEvents = [];
   }

   public function _after(TestInterface $I)
   {
      $this->clearDispatchedDomainEvents();
      \App\Domain\Common\DomainEventDispatcher::instance()->clear();
   }

   public function grabDomainEvents(): array
   {
      return $this->subscriber->recordedEvents;
   }

   public function _domainEventExists(string $class, $entity = null)
   {
      return !empty(array_filter($this->grabDomainEvents(), static function (DomainEvent $event) use ($class, $entity) {
         $result = get_class($event) === $class;
         if ($entity !== null) {
            $result &= $event->getEntity() === $entity;
         }
         return $result;
      }));
   }

   public function seeDispatchedDomainEvent(string $class, $entity = null)
   {
      Assert::assertTrue($this->_domainEventExists($class, $entity), "Event $class haven't been dispatched");
   }

   public function dontSeeDispatchedDomainEvent(string $class)
   {
      Assert::assertFalse($this->_domainEventExists($class), "Event $class have been dispatched");
   }

   public function clearDispatchedDomainEvents()
   {
      $this->subscriber->recordedEvents = [];
   }

   public function clearForEntity($entity)
   {
      for ($i = count($this->subscriber->recordedEvents); $i > 0; $i--) {
         /** @var DomainEvent $event */
         $event = $this->subscriber->recordedEvents[$i];
         if ($entity === $event->getEntity()) {
            unset($this->subscriber->recordedEvents[$i]);
         }
      }
   }

}
