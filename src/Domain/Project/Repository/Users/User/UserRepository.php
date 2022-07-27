<?php


namespace App\Domain\Project\Repository\Users\User;

use App\Domain\Common\EntityNotFoundException;
use App\Domain\Project\Entity\Users\User\Email;
use App\Domain\Project\Entity\Users\User\Id;
use App\Domain\Project\Entity\Users\User\User;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\Persistence\ObjectRepository;

class UserRepository
{

   /**
    * @var ObjectRepository
    */
   private ObjectRepository $repo;
   /**
    * @var EntityManagerInterface
    */
   private EntityManagerInterface $em;

   public function __construct(EntityManagerInterface $em)
   {
      $this->repo = $em->getRepository(User::class);
      $this->em = $em;
   }

   public function find(Id $userId): ?User
   {
      /** @var User|null $user */
      $user = $this->repo->find($userId);

      return $user;
   }

   public function findByEmail(Email $email): ?User
   {
      /** @var User|null $user */
      $user = $this->repo->findOneBy(['email' => $email]);

      return $user;
   }

   public function add(User $user)
   {
      $this->em->persist($user);
   }

   public function remove(User $user)
   {
      $this->em->remove($user);
   }

   public function get(Id $userId): User
   {
      $user = $this->find($userId);
      if (!$user instanceof User) {
         throw new EntityNotFoundException("User with id {$userId} is not found");
      }

      return $user;
   }
}
