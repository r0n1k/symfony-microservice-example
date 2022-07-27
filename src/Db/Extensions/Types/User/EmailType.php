<?php


namespace App\Db\Extensions\Types\User;

use App\Domain\Project\Entity\Users\User\Email;
use Doctrine\DBAL\Platforms\AbstractPlatform;
use Doctrine\DBAL\Types\StringType;

class EmailType extends StringType
{
   public const NAME = 'user_email';

   public function convertToPHPValue($value, AbstractPlatform $platform)
   {
      return !empty($value) ? new Email($value) : null;
   }

   public function convertToDatabaseValue($value, AbstractPlatform $platform)
   {
      return $value instanceof Email ? $value->getValue() : $value;
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
