<?php


namespace App\Db\Extensions\Types\ProjectUserAssignment;

use App\Domain\Project\Entity\Users\ProjectUserAssignment\Role;
use Doctrine\DBAL\Platforms\AbstractPlatform;
use Doctrine\DBAL\Types\StringType;

class RoleType extends StringType
{

   public const NAME = 'project_user_assignment_role';

   public function convertToPHPValue($value, AbstractPlatform $platform)
   {
      return !empty($value) ? new Role($value) : null;
   }

   public function convertToDatabaseValue($value, AbstractPlatform $platform)
   {
      return $value instanceof Role ? (string)$value : null;
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
