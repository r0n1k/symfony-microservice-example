<?php


namespace App\Domain\Template\Entity\TemplateParagraph;

use OpenApi\Annotations as OA;
use Webmozart\Assert\Assert;

class TemplateBlockKind
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
   protected $value;

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
}
