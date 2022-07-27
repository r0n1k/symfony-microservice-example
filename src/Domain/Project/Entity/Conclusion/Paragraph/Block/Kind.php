<?php


namespace App\Domain\Project\Entity\Conclusion\Paragraph\Block;

use Webmozart\Assert\Assert;
use OpenApi\Annotations as OA;

class Kind
{

   public const TEXT = 'text';
   public const DICT = 'dict';

   /**
    * @OA\Schema(schema="ConclusionBlockKind", type="string", enum={
    *    "text",
    *    "dict",
    * })
    * @var string
    */
   protected string $value;

   public function __construct(string $kind)
   {
      Assert::oneOf($kind, [
         self::TEXT,
         self::DICT,
      ]);
      $this->value = $kind;
   }

   public static function dict()
   {
      return new self(self::DICT);
   }

   public static function text()
   {
      return new self(self::TEXT);
   }


   public function getValue()
   {
      return $this->value;
   }

   public function __toString()
   {
      return is_string($this->value) ? $this->value : '';
   }

   public function isText()
   {
      return $this->value === self::TEXT;
   }

   public function isDict()
   {
      return $this->value === self::DICT;
   }

   public function isEqual(self $anotherKind)
   {
      return $anotherKind->value === $this->value;
   }
}
