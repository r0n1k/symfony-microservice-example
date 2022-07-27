<?php


namespace App\Services\Authentication\JWT;

use App\Domain\Common\Flusher;
use App\Domain\Project\Entity\Users\User\FullName;
use App\Domain\Project\Entity\Users\User\Id;
use App\Domain\Project\Repository\Users\User\UserRepository;
use App\Domain\Project\Entity\Users\User\User;
use App\Domain\Project\Entity\Users\User\Email;
use App\Domain\Project\Entity\Users\User\Role;

class UserUpserter
{

   /**
    * @var UserRepository
    */
   private UserRepository $users;
   /**
    * @var Flusher
    */
   private Flusher $flusher;

   public function __construct(UserRepository $users, Flusher $flusher)
   {
      $this->users = $users;
      $this->flusher = $flusher;
   }

   public function upsert($credentials)
   {
      $id = new Id($credentials['user_id']);
      $email = new Email($credentials['email']);
      $role = new Role($credentials['role']);
      $fullName = new FullName($credentials['full_name']);

      $user = $this->users->find($id) ?: new User($id, $fullName, $email, $role);

      $this->users->add($user);
      $this->flusher->flush();
   }
}
