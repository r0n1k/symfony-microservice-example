<?php


namespace App\Http\Formatter\Formatters\Conclusion\Paragraph\Block\Dictionary;

use App\Domain\Project\Entity\Dictionary\Dictionary;
use App\Domain\Project\Repository\Dictionary\DictionaryRepository;
use App\Http\Formatter\Base\EntityFormatter;
use App\Http\Formatter\Base\FormatEvent;
use App\Services\EntityLogger\Repository\EntityLogRepository;
use OpenApi\Annotations as OA;

/**
 * @noinspection PhpUnused
 */

class DictionaryFormatter extends EntityFormatter
{

   /**
    * @var DictionaryRepository
    */
   private DictionaryRepository $dictionaries;
    private EntityLogRepository $logRepository;

    public function __construct(DictionaryRepository $dictionaries, EntityLogRepository $logRepository)
   {
      $this->dictionaries = $dictionaries;
       $this->logRepository = $logRepository;
   }

   /**
    * @param Dictionary $dictionary
    * @return array
    */
   public function format($dictionary)
   {
      /**
       * @OA\Schema(schema="Dictionary", type="object",
       *    @OA\Property(property="key", type="string"),
       *    @OA\Property(property="value", type="string"),
       *    @OA\Property(property="name", type="string"),
       * )
       */
      return [
         'key' => (string)$dictionary->getKey(),
         'value' => $dictionary->getValue() ?? $this->getValue($dictionary),
         'name' => $dictionary->getName(),
          'logs' => $this->logRepository->findAllForEntity($dictionary)
      ];
   }

   /**
    * @inheritDoc
    */
   protected function supports(FormatEvent $event): bool
   {
      return $event->getFormattableData() instanceof Dictionary;
   }

   private function getValue(Dictionary $dictionary)
   {
      return $this->dictionaries->findByProjectAndKey($dictionary->getProject(), $dictionary->getKey())->getValue();
   }
}
