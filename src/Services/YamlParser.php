<?php


namespace App\Services;


use App\Domain\Common\Service\YamlParserInterface;
use Symfony\Component\Yaml\Yaml;

class YamlParser implements YamlParserInterface
{

   /**
    * @inheritDoc
    */
   public function parse(string $input)
   {
      return Yaml::parse($input);
   }
}
