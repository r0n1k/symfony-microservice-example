<?php


namespace App\Db\Extensions\Types\Project;

use App\Domain\Project\Entity\Project\Name;
use Doctrine\DBAL\Platforms\AbstractPlatform;
use Doctrine\DBAL\Types\StringType;

class NameType extends StringType
{
   public const NAME = 'project_name';

   public function convertToDatabaseValue($value, AbstractPlatform $platform)
   {
      return $value instanceof Name ? $value->getValue() : $value;
   }

   public function convertToPHPValue($value, AbstractPlatform $platform)
   {
      return !empty($value) ? new Name($value) : null;
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
