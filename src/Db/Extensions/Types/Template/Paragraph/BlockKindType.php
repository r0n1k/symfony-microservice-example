<?php


namespace App\Db\Extensions\Types\Template\Paragraph;

use App\Domain\Template\Entity\TemplateParagraph\TemplateBlockKind;
use Doctrine\DBAL\Platforms\AbstractPlatform;
use Doctrine\DBAL\Types\StringType;

class BlockKindType extends StringType
{
   public const NAME = 'template_paragraph_blockkind';

   public function convertToPHPValue($value, AbstractPlatform $platform)
   {
      return !empty($value) ? new TemplateBlockKind($value) : null;
   }

   public function convertToDatabaseValue($value, AbstractPlatform $platform)
   {
      return $value instanceof TemplateBlockKind ? $value->getValue() : $value;
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
