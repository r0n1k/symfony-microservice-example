<?php


namespace App\Tests\Helper\MockClasses;



use App\Http\Services\Realtime\WebsocketClientInterface;

class TestWebsocketClient implements WebsocketClientInterface
{
   public function sendEvent(string $eventName, ?array $data = [], ?array $userIds = null): void
   {
      // TODO: Implement sendEvent() method.
   }
}
