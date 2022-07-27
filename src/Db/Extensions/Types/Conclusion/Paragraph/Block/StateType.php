<?php


namespace App\Db\Extensions\Types\Conclusion\Paragraph\Block;

use App\Domain\Project\Entity\Conclusion\Paragraph\Block\State;
use Doctrine\DBAL\Platforms\AbstractPlatform;
use Doctrine\DBAL\Types\StringType;

class StateType extends StringType
{
   public const NAME = 'conclusion_paragraph_block_state';

   public function convertToPHPValue($value, AbstractPlatform $platform)
   {
      return !empty($value) ? new State($value) : null;
   }

   public function convertToDatabaseValue($value, AbstractPlatform $platform)
   {
      return $value instanceof State ? $value->getValue() : $value;
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
