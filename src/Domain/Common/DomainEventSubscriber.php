<?php


namespace App\Domain\Common;


interface DomainEventSubscriber
{
   public function handle(DomainEvent $event): void;
}

