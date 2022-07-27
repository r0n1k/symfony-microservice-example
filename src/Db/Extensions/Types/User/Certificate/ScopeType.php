<?php


namespace App\Db\Extensions\Types\User\Certificate;

use App\Domain\Project\Entity\Users\User\Certificate\Scope;
use Doctrine\DBAL\Platforms\AbstractPlatform;
use Doctrine\DBAL\Types\StringType;

class ScopeType extends StringType
{

   public const NAME = 'user_certificate_scope';

   public function convertToPHPValue($value, AbstractPlatform $platform)
   {
      return !empty($value) ? new Scope($value) : null;
   }

   public function convertToDatabaseValue($value, AbstractPlatform $platform)
   {
      return $value instanceof Scope ? $value->getValue() : $value;
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
