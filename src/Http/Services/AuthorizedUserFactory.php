<?php


namespace App\Http\Services;


use App\Domain\Project\Entity\Users\User\Id;
use App\Domain\Project\Entity\Users\User\User;
use App\Domain\Project\Repository\Users\User\UserRepository;
use App\Services\Authentication\UserIdentity;
use Symfony\Component\Security\Core\Security;

class AuthorizedUserFactory
{

   /**
    * @var Security
    */
   private Security $security;
   /**
    * @var UserRepository
    */
   private UserRepository $users;

   public function __construct(Security $security, UserRepository $users)
   {
      $this->security = $security;
      $this->users = $users;
   }

   public function getUser(): ?User
   {
      $userIdentity = $this->security->getUser();
      if (!$userIdentity instanceof UserIdentity) {
         return null;
      }

      $userId = Id::of($userIdentity->getId());
      return $this->users->find($userId);
   }

}
