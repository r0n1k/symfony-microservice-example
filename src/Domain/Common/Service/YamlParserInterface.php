<?php


namespace App\Domain\Common\Service;


interface YamlParserInterface
{

   /**
    * @param string $input
    * @return mixed
    */
   public function parse(string $input);

}
