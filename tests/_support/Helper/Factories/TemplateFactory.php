<?php


namespace App\Tests\_support\Helper\Factories;


use App\Domain\Template\Entity\Id;
use App\Domain\Template\Entity\Template;
use App\Domain\Template\Entity\Title;
use App\Tests\Helper\DataFactory;
use Faker\Factory;

class TemplateFactory
{

   public static function build(DataFactory $factory, $data = [])
   {
      $faker = Factory::create();

      $title = $data['title'] ?? new Title($faker->name);

      return new Template(Id::next(), $title, false);
   }

}
