<?php


namespace App\Db\Extensions\Types\Template;

use App\Domain\Template\Entity\Id;
use Doctrine\DBAL\Platforms\AbstractPlatform;
use Doctrine\DBAL\Types\GuidType;

class IdType extends GuidType
{
   public const NAME = 'template_id';

   public function convertToDatabaseValue($value, AbstractPlatform $platform)
   {
      return $value instanceof Id ? $value->getValue() : $value;
   }

   public function convertToPHPValue($value, AbstractPlatform $platform)
   {
      return !empty($value) ? new Id($value) : null;
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
