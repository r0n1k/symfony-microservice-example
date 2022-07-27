<?php


namespace App\Http\ReadModel;


use App\Domain\Project\Entity\Conclusion\Conclusion;
use App\Http\Formatter\Objects\DictionaryCollection;
use Doctrine\DBAL\Connection;
use Doctrine\DBAL\FetchMode;
use Doctrine\ORM\EntityManagerInterface;

class ConclusionDictionariesFetcher
{

   /**
    * @var Connection
    */
   private Connection $connection;

   public function __construct(EntityManagerInterface $em)
   {
      $this->connection = $em->getConnection();
   }

   /**
    * @param Conclusion $conclusion
    * @return DictionaryObject[]|DictionaryCollection
    */
   public function fetch(Conclusion $conclusion)
   {
      $project = $conclusion->getProject();
      $query = $this->connection->createQueryBuilder()
         ->select('dict.key', 'dict.value')
         ->from('conclusion_dictionary', 'dict')
         ->where('dict.block_id is null')
         ->andWhere('dict.project_id = :projectId')
         ->setParameter(':projectId', $project->getId()->getValue());

      $stmt = $query->execute();
      $stmt->setFetchMode(FetchMode::CUSTOM_OBJECT, DictionaryObject::class);

      return new DictionaryCollection($stmt->fetchAll());
   }

}
