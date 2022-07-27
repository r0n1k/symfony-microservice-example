<?php


namespace App\Http\Formatter\Formatters;


use App\Domain\Project\Entity\Dictionary\Dictionary;
use App\Domain\Project\Entity\Dictionary\Key;
use App\Domain\Project\Service\DictionaryKeyTranslator;
use App\Http\Formatter\Base\FormatEvent;
use App\Http\Formatter\Base\ObjectFormatter;
use App\Http\Formatter\Objects\DictionaryCollection;
use App\Services\EntityLogger\Repository\EntityLogRepository;

/** @noinspection PhpUnused */
class DictionaryCollectionFormatter extends ObjectFormatter
{

   /**
    * @var DictionaryKeyTranslator
    */
   private DictionaryKeyTranslator $translator;
    private EntityLogRepository $logRepository;

    public function __construct(DictionaryKeyTranslator $translator, EntityLogRepository $logRepository)
   {
      $this->translator = $translator;
       $this->logRepository = $logRepository;
   }

   /**
    * @inheritDoc
    * @param DictionaryCollection $formattableData
    */
   public function format($formattableData)
   {
      $dictionaries = $formattableData->toArray();
      $result = [];
      foreach ($dictionaries as $dictionary) {
         $key = $dictionary->key;
         $keyParts = explode('.', $key);
         $mainKey = $keyParts[0];
         if (count($keyParts) > 2) {
            $subKey = implode('.', array_slice($keyParts, 0, -1));
         } else {
            $subKey = '';
         }
         $resultItem = [
            'key' => $key,
            'value' => $dictionary->value,
            'dictionary_title' => $this->translator->translate(Key::of($key)),
            'dictionary_main_title' => $this->translator->translate(Key::of($mainKey)),
            'dictionary_subtitle' => $this->translator->translate(Key::of($subKey)),
             'logs' => $this->logRepository->findAllByClassAndId(Dictionary::class, $dictionary->id)
         ];
         $result[] = $resultItem;
      }
      return $result;
   }

   /**
    * @inheritDoc
    */
   protected function supports(FormatEvent $event): bool
   {
      return $event->getFormattableData() instanceof DictionaryCollection;
   }
}
