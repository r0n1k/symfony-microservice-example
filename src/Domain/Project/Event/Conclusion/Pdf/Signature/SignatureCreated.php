<?php


namespace App\Domain\Project\Event\Conclusion\Pdf\Signature;


use App\Domain\Common\DomainEvent;
use App\Domain\Project\Entity\Conclusion\Pdf\Signature\Signature;

class SignatureCreated extends DomainEvent
{

   public function __construct(Signature $signature)
   {
      $this->entity = $signature;
      parent::__construct();
   }

   public function getConclusion(): Signature
   {
      return $this->entity;
   }

}
