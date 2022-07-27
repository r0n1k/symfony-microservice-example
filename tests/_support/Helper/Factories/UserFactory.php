<?php

namespace App\Tests\Helper\Factories;

// here you can define custom actions
// all public methods declared in helper class will be available in $I

use App\Domain\Project\Entity\Users\User\FullName;
use App\Domain\Project\Entity\Users\User\Id;
use App\Domain\Project\Entity\Users\User\User;
use App\Domain\Project\Entity\Users\User\Email;
use App\Domain\Project\Entity\Users\User\Role;
use App\Domain\Project\Repository\Users\User\UserRepository;
use App\Tests\Helper\DataFactory;
use Faker\Factory;

class UserFactory
{

   public static function build(DataFactory $factory, $data = [])
   {
      $faker = Factory::create();
      /** @var UserRepository $repo */
      $repo = $factory->_getContainer()->get(UserRepository::class);

      $id = new Id($faker->randomNumber(6));
      $fullName = new FullName($faker->name);
      $email = new Email($faker->email);
      $role = $data['role'] ?? new Role($faker->randomElement(['client', 'expert', 'admin', 'project_manager', 'verifier']));


      return new User($id, $fullName, $email, $role);
   }

}
