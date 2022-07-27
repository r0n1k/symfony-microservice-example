<?php


namespace App\Db\Extensions\Types\Conclusion;

use App\Domain\Project\Entity\Conclusion\Revision;
use Doctrine\DBAL\Platforms\AbstractPlatform;
use Doctrine\DBAL\Types\IntegerType;

class RevisionType extends IntegerType
{
   public const NAME = 'conclusion_revision';

   public function convertToPHPValue($value, AbstractPlatform $platform)
   {
      return is_int($value) ? new Revision($value) : null;
   }

   public function convertToDatabaseValue($value, AbstractPlatform $platform)
   {
      return $value instanceof Revision ? $value->getValue() : $value;
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
