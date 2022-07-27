<?php


namespace App\Domain\Project\Repository\Dictionary;


use App\Domain\Common\EntityNotFoundException;
use App\Domain\Project\Entity\Conclusion\Paragraph\Block\Block;
use App\Domain\Project\Entity\Dictionary\Dictionary;
use App\Domain\Project\Entity\Dictionary\Id;
use App\Domain\Project\Entity\Dictionary\Key;
use App\Domain\Project\Entity\Project\Project;
use Doctrine\DBAL\Connection;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\NonUniqueResultException;
use Doctrine\Persistence\ObjectRepository;

class DictionaryRepository
{

   /**
    * @var Connection
    */
   private Connection $connection;
   /**
    * @var EntityManagerInterface
    */
   private EntityManagerInterface $em;
   /**
    * @var ObjectRepository
    */
   private ObjectRepository $repo;

   public function __construct(EntityManagerInterface $em, Connection $connection)
   {
      $this->connection = $connection;
      $this->repo = $em->getRepository(Dictionary::class);
      $this->em = $em;
   }

   public function nextId(): Id
   {
      /** @noinspection PhpUnhandledExceptionInspection */
      $rawId = $this->connection->query("SELECT nextval('paragraph_block_dictionary_id_seq')")->fetchColumn();
      return new Id($rawId);
   }

   public function get(Id $id): Dictionary
   {
      $dict = $this->find($id);
      if (!$dict instanceof Dictionary) {
         throw new EntityNotFoundException('Dictionary not found');
      }

      return $dict;
   }

   public function find(Id $id): ?Dictionary
   {
      /** @var Dictionary|null $dict */
      $dict = $this->repo->find($id);
      return $dict;
   }

   public function remove(Dictionary $dictionary)
   {
      $this->em->remove($dictionary);
   }

   public function add(Dictionary $dictionary)
   {
      $this->em->persist($dictionary);
   }

   /**
    * @param string $name
    * @param Block $block
    * @return Dictionary|null
    * @throws NonUniqueResultException
    */
   public function findWithNameForBlock(string $name, Block $block): ?Dictionary
   {
      $qb = $this->em->createQueryBuilder();

      $qb
         ->from(Dictionary::class, 'dict')
         ->select('dict')
         ->leftJoin('dict.block', 'block')
         ->where('dict.name = :name')
         ->andWhere('block.id = :block_id')
         ->setParameter(':name', $name)
         ->setParameter(':block_id', $block->getId());

      return $qb
         ->getQuery()
         ->getOneOrNullResult();
   }

   /**
    * @param Key $path
    * @param Block $block
    * @return Dictionary|null
    * @throws NonUniqueResultException
    */
   public function findWithPathForBlock(Key $path, Block $block): ?Dictionary
   {
      $qb = $this->em->createQueryBuilder();

      $qb
         ->from(Dictionary::class, 'dict')
         ->select('dict')
         ->leftJoin('dict.block', 'block')
         ->where($qb->expr()->andX(
            $qb->expr()->eq('dict.path', ':path'),
            $qb->expr()->eq('block.id', ':block_id')
         ))
         ->setParameter(':path', $path->getValue())
         ->setParameter(':block_id', $block->getId());

      return $qb
         ->getQuery()
         ->getOneOrNullResult();
   }

   /**
    * @param Project $project
    * @param Key $key
    * @return Dictionary|null
    */
   public function findByProjectAndKey(Project $project, Key $key): ?Dictionary
   {
      /** @var Dictionary|null $dictionary */
      $dictionary = $this->repo->findOneBy([
         'project' => $project,
         'key' => $key,
         'block' => null,
      ]);

      return $dictionary;
   }

   /**
    * @param Block $block
    * @return Dictionary[]
    */
   public function findByBlock(Block $block): array
   {
      return $this->repo->findBy(['block' => $block]);
   }

   public function findByProject(Project $project)
   {
      return $this->repo->findBy(['project' => $project, 'block' => null]);
   }

   /**
    * @param Block $block
    * @param Key $key
    * @return Dictionary|null
    */
   public function findByBlockAndKey(Block $block, Key $key): ?Dictionary
   {
      /** @var Dictionary|null $dictionary */
      $dictionary = $this->repo->findOneBy(['block' => $block, 'key' => $key]);

      return $dictionary;
   }

}
