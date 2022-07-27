<?php
namespace App\Tests;

// here you can define custom actions
// all public methods declared in helper class will be available in $I

use Faker\Factory;
use Faker\Generator;

class Faker extends \Codeception\Module
{
   /**
    * @var Generator
    */
   public $faker;

   /**
    * @return Generator
    */
   public function faker(): Generator
   {
      return $this->faker;
   }

   public function _beforeSuite($settings = [])
   {
      $this->faker = Factory::create();
   }

}
