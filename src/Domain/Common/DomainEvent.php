<?php


namespace App\Domain\Common;


use DateTime;

abstract class DomainEvent
{
   /**
    * @var DateTime
    */
   private DateTime $occurredOn;

   /**
    * @var object Entity
    */
   protected object $entity;

   public function __construct()
   {
      $this->occurredOn = new DateTime();
   }

   public function getOccurredOn()
   {
      return $this->occurredOn;
   }

    public function getEntity()
    {
       return $this->entity;
    }
}
