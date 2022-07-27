<?php


namespace App\Services\Authentication;

use Symfony\Component\Security\Core\User\EquatableInterface;
use Symfony\Component\Security\Core\User\UserInterface;

class UserIdentity implements UserInterface, EquatableInterface
{

   /**
    * @var int
    */
   private int $id;
   /**
    * @var string
    */
   private string $email;
   /**
    * @var string
    */
   private string $full_name;
   /**
    * @var string
    */
   private string $role;

   public function __construct(
       int $id,
       string $email,
       string $full_name,
       string $role
   ) {
      $this->id = $id;
      $this->email = $email;
      $this->full_name = $full_name;
      $this->role = $role;
   }

   public function getId()
   {
      return $this->id;
   }

   public function getEmail()
   {
      return $this->email;
   }

   public function getFullName()
   {
      return $this->full_name;
   }

   /**
    * @inheritDoc
    */
   public function isEqualTo(UserInterface $user)
   {
      if (!$user instanceof self) {
         return false;
      }

      return $user->getId() === $this->getId();
   }

   /**
    * @inheritDoc
    */
   public function getRoles()
   {
      return [$this->role];
   }

   /**
    * @inheritDoc
    */
   public function getPassword()
   {
      return null;
   }

   /**
    * @inheritDoc
    */
   public function getSalt()
   {
      return null;
   }

   /**
    * @inheritDoc
    */
   public function getUsername()
   {
      return $this->id;
   }

   /**
    * @inheritDoc
    */
   public function eraseCredentials()
   {
      return null;
   }
}
