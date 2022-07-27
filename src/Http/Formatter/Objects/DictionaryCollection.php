<?php


namespace App\Http\Formatter\Objects;


use App\Http\ReadModel\DictionaryObject;
use Iterator;
use Webmozart\Assert\Assert;

class DictionaryCollection implements Iterator
{
   /**
    * @var array
    */
   private array $elements;
   /**
    * @var int
    */
   private int $position = 0;

   public function __construct(array $elements = [])
   {
      Assert::allIsInstanceOf($elements, DictionaryObject::class);
      $this->elements = $elements;
   }

   public function add($element)
   {
      Assert::isInstanceOf($element, DictionaryObject::class);
      $this->elements[] = $element;
   }

   /**
    * @return DictionaryObject[]
    */
   public function toArray()
   {
      return $this->elements;
   }

   /**
    * @inheritDoc
    */
   public function current()
   {
      return $this->elements[$this->position];
   }

   /**
    * @inheritDoc
    */
   public function next()
   {
      ++$this->position;
   }

   /**
    * @inheritDoc
    */
   public function key()
   {
      return $this->position;
   }

   /**
    * @inheritDoc
    */
   public function valid()
   {
      return isset($this->elements[$this->position]);
   }

   /**
    * @inheritDoc
    */
   public function rewind()
   {
      $this->position = 0;
   }
}
