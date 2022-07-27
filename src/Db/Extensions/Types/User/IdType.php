<?php


namespace App\Db\Extensions\Types\User;

use App\Domain\Project\Entity\Users\User\Id;
use Doctrine\DBAL\Platforms\AbstractPlatform;
use Doctrine\DBAL\Types\IntegerType;

class IdType extends IntegerType
{

   public const NAME = 'user_id';

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
