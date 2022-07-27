<?php


namespace App\Domain\Project\Repository\Conclusion\Paragraph;

use App\Domain\Common\EntityNotFoundException;
use App\Domain\Project\Entity\Conclusion\Conclusion;
use App\Domain\Project\Entity\Conclusion\Paragraph\Id;
use App\Domain\Project\Entity\Conclusion\Paragraph\Paragraph;
use App\Domain\Template\Entity\TemplateParagraph\TemplateParagraph;
use Doctrine\DBAL\Connection;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\Persistence\ObjectRepository;

class ParagraphRepository
{

   /**
    * @var ObjectRepository
    */
   private ObjectRepository $repo;
   /**
    * @var EntityManagerInterface
    */
   private EntityManagerInterface $em;
   /**
    * @var Connection
    */
   private Connection $connection;

   public function __construct(EntityManagerInterface $em, Connection $connection)
   {
      $this->repo = $em->getRepository(Paragraph::class);
      $this->em = $em;
      $this->connection = $connection;
   }

   public function find(Id $paragraphId): ?Paragraph
   {
      /** @var Paragraph|null $paragraph */
      $paragraph = $this->repo->find($paragraphId);
      return $paragraph;
   }

   public function get(Id $paragraphId): Paragraph
   {
      $paragraph = $this->find($paragraphId);

      if (!$paragraph instanceof Paragraph) {
         throw new EntityNotFoundException('Paragraph not found');
      }

      return $paragraph;
   }

   public function existsWithSameNameAndParent(TemplateParagraph $paragraph): bool
   {
       $parent = $paragraph->getParent() ? $paragraph->getParent()->getId()->getValue() : null;
       $p = $this->repo->findOneBy(['title' => $paragraph->getTitle(), 'parent' => $parent]);
       return empty($p) ? false : true;
   }

   /**
    * @param Conclusion $conclusion
    * @return Paragraph[]
    */
   public function findAllByConclusion(Conclusion $conclusion)
   {
      return $this->em->createQueryBuilder()
         ->from(Paragraph::class, 'p')
         ->select('p')
         ->where('p.conclusion = :conclusion')
         ->orderBy('p.sort_order', 'ASC')
         ->orderBy('p.id', 'ASC')
         ->setParameter(':conclusion', $conclusion)
         ->getQuery()
         ->getResult();
   }

   public function add(Paragraph $paragraph)
   {
      $this->em->persist($paragraph);
   }

   public function remove(Paragraph $paragraph)
   {
      $this->em->remove($paragraph);
   }

   /**
    * @return Id
    * @noinspection PhpDocMissingThrowsInspection
    */
   public function nextId(): Id
   {
      /** @noinspection PhpUnhandledExceptionInspection */
      $rawId = $this->connection->query('SELECT nextval(\'paragraph_id_seq\')')->fetchColumn();
      return new Id($rawId);
   }

   public function hasChild(Paragraph $paragraph, Paragraph $needle)
   {
      return $this->recursiveCheckHasChild($paragraph->getChildren(), $needle);
//      $this->em->createQueryBuilder()
//         ->from(Paragraph::class, 'subject')
//         ->select('count(*) > 0')
//         ->leftJoin('subject.children')
   }

   /**
    * @param Paragraph[] $children
    * @param Paragraph $needle
    * @return bool
    */
   private function recursiveCheckHasChild(iterable $children, Paragraph $needle) {
      foreach ($children as $child) {
         if ($child === $needle) {
            return true;
         }
         if ($this->recursiveCheckHasChild($child->getChildren(), $needle)) {
            return true;
         }
      }
      return false;
   }
}
