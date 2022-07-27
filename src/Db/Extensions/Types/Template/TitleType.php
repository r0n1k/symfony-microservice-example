<?php


namespace App\Db\Extensions\Types\Template;

use App\Domain\Template\Entity\Title;
use Doctrine\DBAL\Platforms\AbstractPlatform;
use Doctrine\DBAL\Types\StringType;

class TitleType extends StringType
{
   public const NAME = 'template_title';

   public function convertToPHPValue($value, AbstractPlatform $platform)
   {
      return !empty($value) ? new Title($value) : null;
   }

   public function convertToDatabaseValue($value, AbstractPlatform $platform)
   {
      return $value instanceof Title ? $value->getValue() : $value;
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
