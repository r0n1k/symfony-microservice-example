<?php namespace App\Tests\Services;

use App\Domain\Common\DomainEventDispatcher;
use App\Domain\Project\Subscribers\PersistDictionaryValuesOnNewConclusion;
use App\Tests\FunctionalTester;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Event\RequestEvent;
use Symfony\Component\HttpKernel\KernelEvents;

class DomainSubscribersCest
{
   public function _before(FunctionalTester $I)
   {
      /** @var EventDispatcherInterface $dispatcher */
      $dispatcher =  $I->grabService(EventDispatcherInterface::class);
      $event = new RequestEvent($I->getSymfonyKernel(), new Request(), 1);
      try {$dispatcher->dispatch($event, KernelEvents::REQUEST);} catch (\Throwable $e) {}
   }

   public function domainSubscribersSubscribed(FunctionalTester $I)
   {
      $dispatcher = DomainEventDispatcher::instance();
      $reflection = new \ReflectionObject($dispatcher);
      $subscribersReflection = $reflection->getProperty('subscribers');
      $subscribersReflection->setAccessible(true);
      $actualSubscribers = $subscribersReflection->getValue($dispatcher);

      $definedSubscribers = [
         PersistDictionaryValuesOnNewConclusion::class,
      ];

      $actualSubscribersClasses = array_map(static function ($obj) {
         return get_class($obj);
      }, $actualSubscribers);

      foreach ($definedSubscribers as $definedSubscriber) {
         $I->assertContains($definedSubscriber, $actualSubscribersClasses, "$definedSubscriber is not subscribed");
      }
   }
}
