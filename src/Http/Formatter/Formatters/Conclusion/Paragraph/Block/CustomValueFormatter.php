<?php


namespace App\Http\Formatter\Formatters\Conclusion\Paragraph\Block;


use App\Domain\Project\Entity\Conclusion\Paragraph\Block\CustomDictionaryValue;
use App\Http\Formatter\Base\EntityFormatter;
use App\Http\Formatter\Base\FormatEvent;
use App\Services\EntityLogger\Repository\EntityLogRepository;

class CustomValueFormatter extends EntityFormatter
{
    private EntityLogRepository $logRepository;

    public function __construct(EntityLogRepository $logRepository)
    {
        $this->logRepository = $logRepository;
    }

    /**
    * @inheritDoc
    * @var CustomDictionaryValue $customValue
    */
   public function format($customValue)
   {
      return [
         'key' => $customValue->getKey(),
         'value' => $customValue->getValue(),
          'logs' => $this->logRepository->findAllForEntity($customValue)
      ];
   }

   /**
    * @inheritDoc
    */
   protected function supports(FormatEvent $event): bool
   {
      return $event->getFormattableData() instanceof CustomDictionaryValue;
   }
}
