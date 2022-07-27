<?php


namespace App\Domain\Project\Entity\Conclusion\Paragraph\Block;


use App\Domain\Common\DomainEventDispatcher;
use App\Domain\Project\Event\Conclusion\Paragraph\Block\BlockChanged;
use Doctrine\ORM\Mapping as ORM;
use DomainException;
use App\Services\EntityLogger\Annotation as Logger;


/**
 * Class CustomDictionaryValue
 * @ORM\Entity()
 * @ORM\Table(uniqueConstraints={
 *    @ORM\UniqueConstraint(columns={"key", "block_id"})
 * })
 */
class CustomDictionaryValue
{

   /**
    * @ORM\Id()
    * @ORM\GeneratedValue(strategy="SEQUENCE")
    * @ORM\Column(type="integer")
    * @var int
    */
   protected int $id;

   /**
    * @ORM\Column(type="text")
    * @var string
    * @Logger\Versioned
    */
   protected string $key;

   /**
    * @ORM\Column(type="text", nullable=false)
    * @var string
    * @Logger\Versioned
    */
   protected string $value = '';

   /**
    * @ORM\ManyToOne(
    *    targetEntity="Block",
    *    inversedBy="customValues",
    * )
    * @ORM\JoinColumn(name="block_id")
    * @var Block
    */
   protected Block $block;

   public function __construct(string $key, string $value, Block $block)
   {
      if (!$block->getKind()->isDict()) {
         throw new DomainException('Cannot create custom dictionary value for non-dictionary block');
      }
      $this->key = $key;
      $this->value = $value;
      $this->block = $block;
   }

   public function getBlock(): Block
   {
      return $this->block;
   }

   public function getKey(): string
   {
      return $this->key;
   }

   public function getValue(): string
   {
      return $this->value;
   }

   public function setValue($newValue): self
   {
      $this->value = $newValue;
      DomainEventDispatcher::instance()->dispatch(new BlockChanged($this->block));
      return $this;
   }

    public function getId(): int
    {
        return $this->id;
    }

    public function setId(int $id): void
    {
        $this->id = $id;
    }
}
