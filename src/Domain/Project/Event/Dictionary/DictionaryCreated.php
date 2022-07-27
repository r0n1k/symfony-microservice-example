<?php

namespace App\Domain\Project\Event\Dictionary;

use App\Domain\Common\DomainEvent;
use App\Domain\Project\Entity\Dictionary\Dictionary;

class DictionaryCreated extends DomainEvent
{

   public function __construct(Dictionary $dictionary)
   {
      $this->entity = $dictionary;
      parent::__construct();
   }

   /**
    * @return Dictionary
    */
   public function getDictionary(): Dictionary
   {
      return $this->entity;
   }

}
