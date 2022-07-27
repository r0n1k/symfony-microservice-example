<?php


namespace App\Http\Formatter\Formatters;


use App\Domain\Project\Entity\Users\User\Certificate\Certificate;
use App\Http\Formatter\Base\EntityFormatter;
use App\Http\Formatter\Base\FormatEvent;

/** @noinspection PhpUnused */
class CertificateFormatter extends EntityFormatter
{

   /**
    * @inheritDoc
    * @param Certificate $certificate
    */
   public function format($certificate)
   {
      return [
         'id' => $certificate->getId()->getValue(),
         'scope' => $certificate->getScope()->getValue(),
      ];
   }

   /**
    * @inheritDoc
    */
   protected function supports(FormatEvent $event): bool
   {
      return $event->getFormattableData() instanceof Certificate;
   }
}
