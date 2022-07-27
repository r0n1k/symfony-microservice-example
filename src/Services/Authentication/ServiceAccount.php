<?php


namespace App\Services\Authentication;


class ServiceAccount extends UserIdentity
{
   public function __construct()
   {
      parent::__construct(-1, '', '', 'service');
   }
}
