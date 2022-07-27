<?php


namespace App\Http\Services\Realtime;


interface WebsocketClientInterface
{
   public function sendEvent(string $eventName, ?array $data = [], ?array $userIds = null): void;
}
