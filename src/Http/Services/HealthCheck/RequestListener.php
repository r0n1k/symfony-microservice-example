<?php


namespace App\Http\Services\HealthCheck;


use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Event\RequestEvent;
use Symfony\Component\HttpKernel\KernelEvents;

class RequestListener implements EventSubscriberInterface
{

   /**
    * @inheritDoc
    */
   public static function getSubscribedEvents()
   {
      return [
         KernelEvents::REQUEST => ['onKernelRequest', 1000],
      ];
   }

   public function onKernelRequest(RequestEvent $event)
   {
      $request = $event->getRequest();
      $uri = $request->getRequestUri();
      if ($uri !== '/healthz' || $request->getHost() !== 'conclusions-backend') {
         return;
      }

      $response = new Response();
      $event->setResponse($response);
   }
}
