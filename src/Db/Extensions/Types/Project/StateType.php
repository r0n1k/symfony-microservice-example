<?php


namespace App\Db\Extensions\Types\Project;

use App\Domain\Project\Entity\Project\State;
use Doctrine\DBAL\Platforms\AbstractPlatform;
use Doctrine\DBAL\Types\StringType;

class StateType extends StringType
{

   public const NAME = 'project_state';

   public function convertToPHPValue($value, AbstractPlatform $platform)
   {
      return !empty($value) ? new State($value) : null;
   }

   public function convertToDatabaseValue($value, AbstractPlatform $platform)
   {
      return $value instanceof State ? (string)$value : null;
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
