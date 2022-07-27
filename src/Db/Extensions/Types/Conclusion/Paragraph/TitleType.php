<?php


namespace App\Db\Extensions\Types\Conclusion\Paragraph;

use App\Domain\Project\Entity\Conclusion\Paragraph\Title;
use Doctrine\DBAL\Platforms\AbstractPlatform;
use Doctrine\DBAL\Types\StringType;

class TitleType extends StringType
{
   public const NAME = 'conclusion_paragraph_title';

   public function convertToPHPValue($value, AbstractPlatform $platform)
   {
      return (!empty($value) || strlen($value) > 0) ? new Title($value) : null;
   }

   public function convertToDatabaseValue($value, AbstractPlatform $platform)
   {
      return $value instanceof Title ? $value->getValue() : $value;
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
