<?php
namespace App\Tests\Helper\Factories;

// here you can define custom actions
// all public methods declared in helper class will be available in $I

use App\Domain\Project\Entity\Project\Project;
use App\Domain\Project\Entity\Project\Id;
use App\Domain\Project\Entity\Project\Name;
use App\Domain\Project\Entity\Project\State;
use App\Tests\Helper\DataFactory;
use Faker\Factory;

class ProjectFactory
{

   public static function build(DataFactory $factory)
   {
      $faker = Factory::create();

      $state = $faker->randomElement(['default', 'submitted', 'experts_wip']);
      return new Project(new Id($faker->uuid), new Name($faker->text(32)), new State($state));
   }


}
