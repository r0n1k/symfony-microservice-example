<?php


namespace App\Http\ReadModel;


use App\Domain\Project\Entity\Conclusion\Paragraph\Block\Block;
use App\Http\Formatter\Objects\DictionaryCollection;
use Doctrine\DBAL\Connection;
use Doctrine\DBAL\FetchMode;
use Doctrine\ORM\EntityManagerInterface;

class BlockDictionariesFetcher
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
    * @param Block $block
    * @return DictionaryObject[]|DictionaryCollection
    */
   public function fetch(Block $block)
   {
      $query = $this->connection->createQueryBuilder()
         ->from('conclusion_conclusion_paragraph_block', 'block')
         ->select('bdict.id as id, bdict.key, coalesce(bdict.value, pdict.value) as value')

         ->leftJoin('block', 'conclusion_dictionary', 'bdict', 'block.id = bdict.block_id')
         ->leftJoin('block',
            'conclusion_dictionary',
            'pdict',
            'pdict.project_id = bdict.project_id and pdict.key = bdict.key and pdict.block_id is null')

         ->where('block.id = :blockId and bdict.key is not null')
         ->setParameter(':blockId', $block->getId()->getValue());

      $stmt = $query->execute();
      $stmt->setFetchMode(FetchMode::CUSTOM_OBJECT, DictionaryObject::class);

      return new DictionaryCollection($stmt->fetchAll());
   }

}
