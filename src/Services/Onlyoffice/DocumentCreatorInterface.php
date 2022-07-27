<?php


namespace App\Services\Onlyoffice;


interface DocumentCreatorInterface
{

   /**
    * @param $path
    * @return mixed
    */
   public function create($path);

}
