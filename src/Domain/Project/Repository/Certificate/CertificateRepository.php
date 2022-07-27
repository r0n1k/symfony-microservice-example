<?php


namespace App\Domain\Project\Repository\Certificate;


use App\Domain\Common\EntityNotFoundException;
use App\Domain\Project\Entity\Users\User\Certificate\Certificate;
use App\Domain\Project\Entity\Users\User\Certificate\Id;
use Doctrine\DBAL\Connection;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\Persistence\ObjectRepository;

class CertificateRepository
{

   /**
    * @var EntityManagerInterface
    */
   private EntityManagerInterface $em;
   /**
    * @var ObjectRepository
    */
   private ObjectRepository $repo;
   /**
    * @var Connection
    */
   private Connection $connection;

   public function __construct(EntityManagerInterface $em)
   {
      $this->em = $em;
      $this->repo = $em->getRepository(Certificate::class);
      $this->connection = $em->getConnection();
   }

   public function findByScope($scope): ?Certificate
   {
      /** @var Certificate|null $certificate */
      $certificate = $this->repo->findOneBy(['scope' => $scope]);
      return $certificate;
   }

   public function getByScope($scope): Certificate
   {
      $certificate = $this->findByScope($scope);
      if (!$certificate) {
         throw new EntityNotFoundException("Certificate with scope $scope not found");
      }

      return $certificate;
   }

   public function add(Certificate $certificate)
   {
      $this->em->persist($certificate);
   }

   public function remove(Certificate $certificate)
   {
      $this->em->remove($certificate);
   }

   /**
    * @return Id
    * @noinspection PhpDocMissingThrowsInspection
    */
   public function nextId(): Id
   {
      /** @noinspection PhpUnhandledExceptionInspection */
      $rawId = $this->connection->query('SELECT nextval(\'certificate_id_seq\')')->fetchColumn();
      return new Id($rawId);
   }

   /**
    * @return Certificate[]
    */
   public function all()
   {
      /** @var Certificate[] $certificates */
      $certificates = $this->repo->findAll();
      return $certificates;
   }

}
