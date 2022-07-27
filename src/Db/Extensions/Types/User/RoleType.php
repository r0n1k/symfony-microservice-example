<?php


namespace App\Db\Extensions\Types\User;

use App\Domain\Project\Entity\Users\User\Role;
use Doctrine\DBAL\Platforms\AbstractPlatform;
use Doctrine\DBAL\Types\StringType;

class RoleType extends StringType
{

   public const NAME = 'user_role';

   public function convertToDatabaseValue($value, AbstractPlatform $platform)
   {
      return $value instanceof Role ? $value->getValue() : $value;
   }

   public function convertToPHPValue($value, AbstractPlatform $platform)
   {
      return !(empty($value)) ? new Role($value) : null;
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
