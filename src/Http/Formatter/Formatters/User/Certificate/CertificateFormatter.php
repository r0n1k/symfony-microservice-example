<?php


namespace App\Http\Formatter\Formatters\User\Certificate;

use App\Domain\Project\Entity\Users\User\Certificate\Certificate;
use App\Http\Formatter\Base\FormatEvent;
use App\Http\Formatter\Base\EntityFormatter;
use OpenApi\Annotations as OA;

/**
 * @noinspection PhpUnused
 */
class CertificateFormatter extends EntityFormatter
{
   /**
    * @OA\Schema(schema="Certificate", type="object",
    *    @OA\Property(property="scope", type="string", nullable=false),
    * )
    * @param Certificate $certificate
    * @return array
    */
   public function format($certificate)
   {

      return [
         'scope' => $certificate->getScope(),
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
