<?php


namespace App\Http\Formatter\Formatters\Project\DictionaryItem;


use App\Domain\Project\Entity\Project\Dictionary\DictionaryItem;
use App\Http\Formatter\Base\EntityFormatter;
use App\Http\Formatter\Base\FormatEvent;
use OpenApi\Annotations as OA;

class DictionaryItemFormatter extends EntityFormatter
{

   /**
    * @inheritDoc
    * @param DictionaryItem $item
    */
   public function format($item)
   {
      /**
       * @OA\Schema(schema="ProjectDictionaryItem", type="object",
       *    @OA\Property(property="key", type="string"),
       *    @OA\Property(property="value", type="string"),
       * )
       */
      return [
         'key' => $item->getKey(),
         'value' => $item->getValue(),
      ];
   }

   /**
    * @inheritDoc
    */
   protected function supports(FormatEvent $event): bool
   {
      return $event->getFormattableData() instanceof DictionaryItem;
   }
}
