<?php


namespace App\Domain\Project\Entity\Conclusion;

use Webmozart\Assert\Assert;
use OpenApi\Annotations as OA;

/**
 * Class ConclusionKind
 * @package App\Domain\Entity\Conclusion
 *
 * Тип заявления: конструктор, загрузка файлов или онлайн редактор
 */
class Kind
{

   /**
    *
    */
   public const GENERATOR = 'generator';
   public const FILES = 'files';
   public const ONLINE = 'online';

   /**
    * @OA\Schema(schema="ConclusionKind", type="string", enum={
    *    "generator",
    *    "files",
    *    "online",
    * })
    * @var string
    */
   protected $value;

   public function __construct(string $kind)
   {
      Assert::notEmpty($kind);
      Assert::oneOf($kind, [self::GENERATOR, self::FILES, self::ONLINE]);
      $this->value = $kind;
   }

   public function getValue(): string
   {
      return $this->value;
   }

   public function __toString()
   {
      return (string)$this->value;
   }
}
