<?php


namespace App\Db\Extensions\Types\User;

use App\Domain\Project\Entity\Users\User\FullName;
use Doctrine\DBAL\Platforms\AbstractPlatform;
use Doctrine\DBAL\Types\StringType;

class FullNameType extends StringType
{
   public const NAME = 'user_fullname';

   public function convertToPHPValue($value, AbstractPlatform $platform)
   {
      return !empty($value) ? new FullName($value) : null;
   }

   public function convertToDatabaseValue($value, AbstractPlatform $platform)
   {
      return $value instanceof FullName ? $value->getValue() : $value;
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
