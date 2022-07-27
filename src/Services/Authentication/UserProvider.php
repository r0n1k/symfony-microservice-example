<?php


namespace App\Services\Authentication;

use App\Domain\Project\Entity\Users\User\Id;
use App\Domain\Project\Repository\Users\User\UserRepository;
use App\Domain\Project\Entity\Users\User\User;
use Symfony\Component\Security\Core\Exception\UnsupportedUserException;
use Symfony\Component\Security\Core\Exception\UsernameNotFoundException;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Security\Core\User\UserProviderInterface;
use function get_class;

class UserProvider implements UserProviderInterface
{

   /**
    * @var UserRepository
    */
   private UserRepository $users;

   public function __construct(UserRepository $users)
   {
      $this->users = $users;
   }

   private static function identityByUser(User $user): UserIdentity
   {
      return new UserIdentity(
          $user->getId()->getValue(),
          $user->getEmail()->getValue(),
          $user->getFullName()->getValue(),
          $user->getRole()->getValue(),
      );
   }

   /**
    * @inheritDoc
    */
   public function loadUserByUsername($id)
   {
      $user = $this->loadUser($id);
      return self::identityByUser($user);
   }

   /**
    * @inheritDoc
    */
   public function refreshUser(UserInterface $identity)
   {
      if (!$identity instanceof UserIdentity) {
         throw new UnsupportedUserException('Invalid user class ' . get_class($identity));
      }

      $user = $this->loadUser($identity->getId());
      return self::identityByUser($user);
   }

   /**
    * @inheritDoc
    */
   public function supportsClass(string $class)
   {
      return $class === UserIdentity::class;
   }

   protected function loadUser($id): User
   {
      $user = $this->users->find(new Id($id));

      if ($user instanceof User) {
         return $user;
      }

      throw new UsernameNotFoundException('');
   }
}
