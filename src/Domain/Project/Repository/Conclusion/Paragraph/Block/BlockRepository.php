<?php


namespace App\Domain\Project\Repository\Conclusion\Paragraph\Block;

use App\Domain\Common\EntityNotFoundException;
use App\Domain\Project\Entity\Conclusion\Conclusion;
use App\Domain\Project\Entity\Conclusion\Paragraph\Block\Block;
use App\Domain\Project\Entity\Conclusion\Paragraph\Block\Id;
use Doctrine\DBAL\Connection;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\Persistence\ObjectRepository;

class BlockRepository
{

   private EntityManagerInterface $em;
   private ObjectRepository $repo;
   private Connection $connection;

   public function __construct(EntityManagerInterface $em, Connection $connection)
   {
      $this->repo = $em->getRepository(Block::class);
      $this->em = $em;
      $this->connection = $connection;
   }

   public function find(Id $blockId): ?Block
   {
      /** @var Block $block */
      $block = $this->repo->find($blockId);

      return $block;
   }

   public function get(Id $blockId): Block
   {
      $block = $this->find($blockId);

      if (!$block instanceof Block) {
         throw new EntityNotFoundException("Block with id $blockId is not found");
      }

      return $block;
   }

   public function add(Block $block)
   {
      $this->em->persist($block);
   }

   public function remove(Block $block)
   {
      $this->em->remove($block);
   }

   /**
    * @return Id
    * @noinspection PhpDocMissingThrowsInspection
    */
   public function nextId(): Id
   {
      /** @noinspection PhpUnhandledExceptionInspection */
      $rawId = $this->connection->query('SELECT nextval(\'paragraph_block_id_seq\')')->fetchColumn();
      return new Id($rawId);
   }

   /**
    * @param Conclusion $conclusion
    * @return array|Block[]
    */
   public function findAllByConclusion(Conclusion $conclusion)
   {
      return $this->em->createQueryBuilder()
         ->from(Block::class, 'b')
         ->select('b')
         ->join('b.paragraph', 'p')
         ->join('p.conclusion', 'c')
         ->where('c.id = :conclusion_id')
         ->setParameter(':conclusion_id', (string)$conclusion->getId())
         ->getQuery()
         ->getResult();
   }

   /**
    * @param $path
    * @return Block|null
    */
   public function findByPath($path): ?Block
   {
      /** @var Block|null $block */
      $block = $this->repo->findOneBy(['filePath.path' => $path]);

      return $block;
   }

   /**
    * @param $path
    * @return Block|null
    */
   public function findByPathKey($path): ?Block
   {
      /** @var Block|null $block */
      $block = $this->repo->findOneBy(['filePath.key' => $path]);

      return $block;
   }
}
