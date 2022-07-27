<?php


namespace App\Domain\Template\Repository\TemplateParagraph;


use App\Domain\Common\EntityNotFoundException;
use App\Domain\Template\Entity\TemplateParagraph\Id;
use App\Domain\Template\Entity\TemplateParagraph\TemplateParagraph;
use Doctrine\DBAL\Connection;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\Persistence\ObjectRepository;

class TemplateParagraphRepository
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

   public function __construct(EntityManagerInterface $em, Connection $connection)
   {
      $this->repo = $em->getRepository(TemplateParagraph::class);
      $this->em = $em;
      $this->connection = $connection;
   }

   public function nextId(): Id
   {
      $rawId = $this->connection->query("select nextval('template_paragraph_id_seq')")->fetchColumn();
      return new Id($rawId);
   }

   public function add(TemplateParagraph $paragraph)
   {
      $this->em->persist($paragraph);
   }

   public function find(Id $id): ?TemplateParagraph
   {
      /** @var TemplateParagraph $paragraph */
      $paragraph = $this->repo->find($id->getValue());

      return $paragraph;
   }

   /**
    * @param Id $id
    * @return mixed
    */
   public function get(Id $id): TemplateParagraph
    {
       $paragraph = $this->find($id);

       if (!$paragraph instanceof TemplateParagraph) {
          throw new EntityNotFoundException('Parent paragraph not found');
       }

       return $paragraph;
    }

}
