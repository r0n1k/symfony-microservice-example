<?php


namespace App\Db\Extensions\Types\Dictionary;

use App\Domain\Project\Entity\Dictionary\Key;
use Doctrine\DBAL\Platforms\AbstractPlatform;
use Doctrine\DBAL\Types\StringType;

class KeyType extends StringType
{
   public const NAME = 'conclusion_paragraph_block_dictionary_path';

   public function convertToPHPValue($value, AbstractPlatform $platform)
   {
      return !empty($value) ? new Key($value) : null;
   }

   public function convertToDatabaseValue($value, AbstractPlatform $platform)
   {
      return $value instanceof Key ? $value->getValue() : $value;
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
