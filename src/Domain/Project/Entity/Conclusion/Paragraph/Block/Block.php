<?php


namespace App\Domain\Project\Entity\Conclusion\Paragraph\Block;

use App\Domain\Common\DomainEventDispatcher;
use App\Domain\Project\Entity\Conclusion\Paragraph\Paragraph;
use App\Domain\Project\Entity\Dictionary\Dictionary;
use App\Domain\Project\Entity\Users\User\User;
use App\Domain\Project\Event\Conclusion\Paragraph\Block\BlockChanged;
use App\Domain\Project\Event\Conclusion\Paragraph\Block\BlockCreated;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;
use App\Services\EntityLogger\Annotation as Logger;
use App\Services\EntitySorter\Annotation as Sorter;
use DomainException;

/**
 * Class ConclusionBlock
 * @package App\Domain\Entity\ConclusionBlock
 *
 * @ORM\Entity()
 * @ORM\Table(name="conclusion_paragraph_block")
 * @Sorter\Sorted(property="order", parent_property="paragraph", deleted_property="state", deleted_value="deleted")
 */
class Block
{

   use BlockStateMachine {
      setState as _setState;
   }

   /**
    * @ORM\Id()
    * @ORM\GeneratedValue(strategy="NONE")
    * @ORM\SequenceGenerator(sequenceName="paragraph_block_id_seq", initialValue=1)
    * @ORM\Column(type="conclusion_paragraph_block_id")
    */
   protected Id $id;

   /**
    * @Logger\Versioned()
    * @ORM\ManyToOne(
    *    targetEntity="\App\Domain\Project\Entity\Conclusion\Paragraph\Paragraph",
    *    inversedBy="blocks",
    *    cascade={"persist", "refresh", "merge"},
    *    fetch="EAGER"
    * )
    * @ORM\JoinColumn(nullable=false, name="paragraph_id")
    */
   protected Paragraph $paragraph;

   /**
    * @Logger\Versioned()
    * @ORM\Column(type="conclusion_paragraph_block_kind")
    */
   protected Kind $kind;

   /**
    * @var Dictionary[]|ArrayCollection
    * @ORM\OneToMany(
    *    targetEntity="App\Domain\Project\Entity\Dictionary\Dictionary",
    *    mappedBy="block",
    *    orphanRemoval=true,
    *    cascade={"persist", "refresh", "remove"},
    *    fetch="EAGER"
    *  )
    */
   protected $dictionaries;

   /**
    * @var CustomDictionaryValue[]|ArrayCollection
    * @ORM\OneToMany(
    *    targetEntity="CustomDictionaryValue",
    *    mappedBy="block",
    *    orphanRemoval=true,
    *    cascade={"all"},
    *    fetch="EAGER"
    * )
    */
   protected $customValues;

   /**
    * @Logger\Versioned()
    * @ORM\ManyToOne(
    *    targetEntity="\App\Domain\Project\Entity\Users\User\User",
    *    inversedBy="conclusionBlocks",
    *    cascade={"persist", "refresh"},
    *    fetch="EAGER"
    * )
    */
   protected ?User $executor = null;

   /**
    * @ORM\Embedded(class="FilePath", columnPrefix="file_")
    */
   protected ?FilePath $filePath = null;

   /**
    * @Logger\Versioned()
    * @var State
    * @ORM\Column(type="conclusion_paragraph_block_state")
    */
   protected State $state;

   /**
    * @Logger\Versioned()
    * @var string|null
    * @ORM\Column(type="text", nullable=true)
    */
   protected ?string $html = null;

    /**
     * @var string|null
     * @ORM\Column(name="preview_html", type="text", nullable=true)
     */
    protected ?string $previewHtml = null;

    /**
     * @var Order
     * @ORM\Column(type="conclusion_paragraph_block_order", name="sort_order", nullable=false)
     * @Logger\Versioned
     */
    protected ?Order $order = null;

    /**
     * @Logger\Versioned()
     * @var string|null
     * @ORM\Column(type="string", nullable=true)
     */
    protected ?string $declineReason = null;

   public function __construct(Id $id, Kind $kind, Paragraph $paragraph, State $state, Order $order)
   {
      $this->id = $id;
      $this->kind = $kind;
      $this->paragraph = $paragraph;
      $this->state = $state;
      $this->dictionaries = new ArrayCollection();
      $this->customValues = new ArrayCollection();
      $this->order = $order;
      $this->publishEvent(BlockCreated::class);
   }

   protected function publishEvent($class, $arg = null) {
      DomainEventDispatcher::instance()->dispatch(new $class($arg ?? $this));
   }

   public function getParagraph(): Paragraph
   {
      return $this->paragraph;
   }

    public function setParagraph(Paragraph $paragraph): self
    {
        $this->paragraph = $paragraph;
        $this->publishEvent(BlockChanged::class);
        return $this;
    }


   public function getId(): Id
   {
      return $this->id;
   }

   public function getKind(): Kind
   {
      return $this->kind;
   }

   public function setTextKind(): self
   {
      $this->paragraph->getConclusion()->ensureCanChange();

      if ($this->kind->getValue() === Kind::TEXT) {
         return $this;
      }

      $this->kind = Kind::text();
      $this->publishEvent(BlockChanged::class);


      return $this;
   }

   public function setDictKind(): self
   {
      $this->paragraph->getConclusion()->ensureCanChange();

      if ($this->kind->getValue() === Kind::DICT) {
         return $this;
      }

      $this->kind = Kind::dict();
      $this->publishEvent(BlockChanged::class);


      return $this;
   }

   public function getState(): State
   {
      return $this->state;
   }

   public function getExecutor(): ?User
   {
      return $this->executor;
   }

   public function setExecutor(?User $executor): self
   {
      $this->paragraph->getConclusion()->ensureCanChange();

      if ($executor instanceof User) {
         if ($this->executor !== null) {
            throw new DomainException("Над блоком уже работает {$this->executor->getFullName()}");
         }

         if (
            $this->state->getValue() === State::WAITING_TO_START ||
            $this->state->getValue() === State::DECLINED
         ) {
            $this->state = State::workInProgress();
         } else {
            throw new DomainException("Нельзя брать блок в работу на этом статусе");
         }
      } else if ($this->state->getValue() === State::WORK_IN_PROGRESS) {
         $this->state = State::initial();
      }

      $this->executor = $executor;
      $this->publishEvent(BlockChanged::class);
      return $this;
   }

   public function setState(State $newState)
   {
      $this->paragraph->getConclusion()->ensureCanChange();

      $this->_setState($newState);
      $this->publishEvent(BlockChanged::class);
   }

   public function getFilePath(): ?FilePath
   {
      return $this->filePath;
   }

   public function setFilePath(?FilePath $filePath): self
   {
      $this->paragraph->getConclusion()->ensureCanChange();

      $this->filePath = $filePath;
      $this->publishEvent(BlockChanged::class);

      return $this;
   }

   public function setHtml(?string $html): self
   {
      $this->paragraph->getConclusion()->ensureCanChange();

      if (!$this->kind->isText()) {
         throw new DomainException('Non-text block cannot have a html');
      }
      $this->publishEvent(BlockChanged::class);
      $this->html = $html;
      return $this;
   }

   public function getHtml(): ?string
   {
      return $this->html;
   }

   public function setCustomValue($key, $value): CustomDictionaryValue
   {
      $this->paragraph->getConclusion()->ensureCanChange();

      if ($customValue = $this->getCustomValue($key)) {
         $customValue->setValue($value);
      } else {
         $customValue = new CustomDictionaryValue($key, $value, $this);
         $this->customValues->add($customValue);
      }

      $this->publishEvent(BlockChanged::class);

      return $customValue;
   }

   public function removeCustomValue($key)
   {
      $this->paragraph->getConclusion()->ensureCanChange();

      $customValue = $this->getCustomValue($key);
      if (!$customValue) {
         throw new DomainException("Block has no custom values with key {$key}");
      }
      $this->customValues->removeElement($customValue);

      $this->publishEvent(BlockChanged::class);

      return $this;
   }

   /**
    * @return CustomDictionaryValue[]|ArrayCollection
    */
   public function getCustomValues()
   {
      return $this->customValues;
   }

   public function getCustomValue($key): ?CustomDictionaryValue
   {
      return $this->customValues->filter(static function (CustomDictionaryValue $value) use ($key) {
         return $value->getKey() === $key;
      })->first() ?: null;
   }

    public function getOrder(): Order
    {
        return $this->order ?: Order::initial();
    }

    public function setOrder(Order $order): self
    {
        $this->paragraph->getConclusion()->ensureCanChange();

        $this->order = $order;
        $this->publishEvent(BlockChanged::class);

        return $this;
    }

    public function getDeclineReason(): ?string
    {
        return $this->declineReason;
    }

    public function setDeclineReason(?string $declineReason): self
    {
        $this->declineReason = $declineReason;
        $this->publishEvent(BlockChanged::class);

        return $this;
    }

    public function getPreviewHtml(): ?string
    {
        return $this->previewHtml;
    }

    public function setPreviewHtml(?string $previewHtml): self
    {
        $this->previewHtml = $previewHtml;
        $this->publishEvent(BlockChanged::class);

        return $this;
    }

}
