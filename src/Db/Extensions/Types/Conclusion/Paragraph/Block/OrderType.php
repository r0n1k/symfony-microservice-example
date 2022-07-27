<?php


namespace App\Db\Extensions\Types\Conclusion\Paragraph\Block;

use App\Domain\Project\Entity\Conclusion\Paragraph\Block\Order;
use Doctrine\DBAL\Platforms\AbstractPlatform;
use Doctrine\DBAL\Types\IntegerType;

class OrderType extends IntegerType
{

   public const NAME = 'conclusion_paragraph_block_order';

   public function convertToDatabaseValue($value, AbstractPlatform $platform)
   {
      return $value instanceof Order ? $value->getValue() : $value;
   }

   public function convertToPHPValue($value, AbstractPlatform $platform)
   {
      return is_int($value) ? new Order($value) : null;
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
