<?php


namespace App\Db\Extensions\Types\Conclusion;

use App\Domain\Project\Entity\Conclusion\TemplateId;
use Doctrine\DBAL\Platforms\AbstractPlatform;
use Doctrine\DBAL\Types\StringType;

class TemplateIdType extends StringType
{
   public const NAME = 'conclusion_template_id';

   public function convertToPHPValue($value, AbstractPlatform $platform)
   {
      return !empty($value) ? new TemplateId($value) : null;
   }

   public function convertToDatabaseValue($value, AbstractPlatform $platform)
   {
      return $value instanceof TemplateId ? $value->getValue() : $value;
   }

   public function getName()
   {
      return self::NAME;
   }

   public function requiresSQLCommentHint(AbstractPlatform $platform) : bool
   {
      return true;
   }
}
