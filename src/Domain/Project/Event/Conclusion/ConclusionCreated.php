<?php


namespace App\Domain\Project\Event\Conclusion;


use App\Domain\Common\DomainEvent;
use App\Domain\Project\Entity\Conclusion\Conclusion;

class ConclusionCreated extends DomainEvent
{

   public function __construct(Conclusion $conclusion)
   {
      $this->entity = $conclusion;
      parent::__construct();
   }

   /**
    * @return Conclusion
    */
   public function getConclusion(): Conclusion
   {
      return $this->entity;
   }

}
