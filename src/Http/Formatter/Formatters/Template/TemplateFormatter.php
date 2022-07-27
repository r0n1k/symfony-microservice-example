<?php

namespace App\Http\Formatter\Formatters\Template;


use App\Domain\Template\Entity\Template;
use App\Http\Formatter\Base\EntityFormatter;
use App\Http\Formatter\Base\FormatEvent;
use OpenApi\Annotations as OA;

/**
 * @noinspection PhpUnused
 */
class TemplateFormatter extends EntityFormatter
{
   /**
    * @param Template $entity
    * @return array
    */
   public function format($entity)
   {
      /**
       * @OA\Schema(schema="ConclusionTemplate", type="object",
       *    @OA\Property(property="id", type="string", format="uuid"),
       *    @OA\Property(property="name", type="string"),
       *    @OA\Property(property="is_basic", type="boolean", description="Является ли базовым шаблоном, иначе пользовательский"),
       * )
       */
      return [
         'id' => $entity->getId(),
         'name' => $entity->getTitle(),
         'is_basic' => $entity->getIsBasic(),
      ];
   }

   /**
    * @inheritDoc
    */
   protected function supports(FormatEvent $event): bool
   {
      return $event->getFormattableData() instanceof Template;
   }
}
