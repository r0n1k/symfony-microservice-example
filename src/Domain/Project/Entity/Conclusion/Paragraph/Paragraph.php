<?php


namespace App\Domain\Project\Entity\Conclusion\Paragraph;

use App\Domain\Common\DomainEventDispatcher;
use App\Domain\Project\Entity\Conclusion\Paragraph\Block\Block;
use App\Domain\Project\Entity\Conclusion\Paragraph\Block\Id as BlockId;
use App\Domain\Project\Entity\Conclusion\Paragraph\Block\Kind;
use App\Domain\Project\Entity\Conclusion\Paragraph\Block\Order as BlockOrder;
use App\Domain\Project\Entity\Conclusion\Paragraph\Block\State;
use App\Domain\Project\Entity\Conclusion\Conclusion;
use App\Domain\Project\Entity\Users\User\Certificate\Certificate;
use App\Domain\Project\Event\Conclusion\Paragraph\Block\BlockDeleted;
use App\Domain\Project\Event\Conclusion\Paragraph\ParagraphChanged;
use App\Domain\Project\Event\Conclusion\Paragraph\ParagraphCreated;
use App\Domain\Project\Event\Conclusion\Paragraph\ParagraphDeleted;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use DomainException;
use App\Services\EntityLogger\Annotation as Logger;
use OpenApi\Annotations as OA;
use App\Services\EntitySorter\Annotation as Sorter;

/**
 * Class ConclusionParagraph
 * @package App\Domain\Entity\ConclusionParagraph
 *
 * @ORM\Entity()
 * @ORM\Table(name="conclusion_paragraph")
 * @Sorter\Sorted(property="order", parent_property="parent", deleted_property=null, deleted_value=null, same_properties="conclusion")
 */
class Paragraph
{

   /**
    * @var Id
    * @ORM\Id()
    * @ORM\Column(type="conclusion_paragraph_id")
    * @ORM\GeneratedValue(strategy="NONE")
    * @ORM\SequenceGenerator(sequenceName="paragraph_id_seq", initialValue=1)
    * @OA\Schema(schema="ConclusionParagraphId", type="integer")
    */
   protected Id $id;

   /**
    * @var self
    * @ORM\ManyToOne(targetEntity="Paragraph", inversedBy="children", cascade={"persist", "refresh"}, fetch="EAGER")
    * @ORM\JoinColumn(onDelete="CASCADE", nullable=true)
    * @Logger\Versioned
    */
   private ?self $parent = null;

   /**
    * @ORM\OneToMany(targetEntity="Paragraph", mappedBy="parent", cascade={"all"}, orphanRemoval=true, fetch="EAGER")
    * @var self[]|ArrayCollection
    */
   private $children;

   /**
    * @ORM\OneToMany(
    *    targetEntity="\App\Domain\Project\Entity\Conclusion\Paragraph\Block\Block",
    *    mappedBy="paragraph",
    *    orphanRemoval=true,
    *    cascade={"persist", "refresh", "remove"},
    *    fetch="EAGER"
    * )
    * @var Block[]|ArrayCollection
    */
   protected $blocks;

   /**
    * @var Conclusion
    * @ORM\ManyToOne(
    *    targetEntity="\App\Domain\Project\Entity\Conclusion\Conclusion",
    *    inversedBy="paragraphs",
    *    cascade={"persist", "refresh"},
    *    fetch="EAGER"
    * )
    */
   protected Conclusion $conclusion;

   /**
    * @var Title
    * @ORM\Column(type="conclusion_paragraph_title")
    * @Logger\Versioned
    */
   protected Title $title;

   /**
    * @var Order
    * @ORM\Column(type="conclusion_paragraph_order", name="sort_order", nullable=false)
    * @Logger\Versioned
    */
   protected ?Order $order = null;


   /**
    * @var ArrayCollection|Certificate[]
    * @ORM\ManyToMany(targetEntity="\App\Domain\Project\Entity\Users\User\Certificate\Certificate")
    * @ORM\JoinTable(name="paragraph_certificates",
    *    joinColumns={@ORM\JoinColumn(name="paragraph_id", referencedColumnName="id")},
    *    inverseJoinColumns={@ORM\JoinColumn(name="certificate_id", referencedColumnName="id")}
    * )
    */
   protected $certificates;

   /**
    * Paragraph constructor.
    * @param Id $id
    * @param Conclusion $conclusion
    * @param Title $title
    * @param Order $order
    * @param Paragraph $parent
    */
   public function __construct(Id $id, Conclusion $conclusion, Title $title, ?Order $order, self $parent = null)
   {
      $this->children = new ArrayCollection();
      $this->id = $id;
      $this->conclusion = $conclusion;
      $this->title = $title;
      $this->order = $order ?: Order::initial();
      $this->parent = $parent;
      $this->certificates = new ArrayCollection();
      $this->blocks = new ArrayCollection();
      $this->dispatchEvent(ParagraphCreated::class);
   }

   public function getParent(): ?self
   {
      return $this->parent;
   }

   public function setParent(?self $parent): self
   {
      $this->conclusion->ensureCanChange();

      if ($parent && $parent->getConclusion() !== $this->getConclusion()) {
         throw new DomainException('Cannot set parent to paragraph from another conclusion');
      }
      if ($parent === $this || $this->parent === $parent) {
          return $this;
      }
      /* я хуй знает что тут за логика, т.к. парент параграфа всегда instanceof self == true
       * if ($this->parent instanceof self) {
         $this->parent->children->removeElement($this);
      }*/
       if($this->myChild($parent)){
           throw new DomainException('You cannot make a parent a child of a child');
       }
      $this->parent = $parent;
      if ($parent && !$this->parent->children->contains($this)) {
         $this->parent->children->add($this);
      }

      $this->dispatchEvent(ParagraphChanged::class);
      return $this;
   }

    private function myChild(self $newParent): bool
    {
        $parents = [$this->getId()->getValue()];
        $parent = $newParent->getParent();
        while($parent){
            $parents[] = $parent->getId()->getValue();
            $parent = $parent->getParent();
        }
        return in_array($newParent->getId()->getValue(), $parents) ? true : false;
    }

   /**
    * @return Collection|Paragraph[]
    */
   public function getChildren(): Collection
   {
      return $this->children;
   }

   public function addChild(Id $id, Title $title, Order $order = null): self
   {
      $this->conclusion->ensureCanChange();

      $child = new self($id, $this->conclusion, $title, $order);
      if (!$this->children->contains($child)) {
         $this->children[] = $child;
         $child->setParent($this);
      }

      $this->dispatchEvent(ParagraphChanged::class);
      return $child;
   }

   public function removeChild(Paragraph $child): self
   {
      $this->conclusion->ensureCanChange();

      if ($this->children->contains($child)) {
         $this->children->removeElement($child);
         // set the owning side to null (unless already changed)
         if ($child->getParent() === $this) {
            $child->setParent(null);
         }

         $this->dispatchEvent(ParagraphChanged::class);
         $this->dispatchEvent(ParagraphDeleted::class, $child);
      }

      return $this;
   }

   /**
    * @return Block[]|ArrayCollection
    */
   public function getBlocks()
   {
      return $this->blocks;
   }

   public function addBlock(BlockId $id, Kind $kind, State $state, BlockOrder $order): Block
   {
      $this->conclusion->ensureCanChange();

      $this->blocks->add($block = new Block($id, $kind, $this, $state, $order));

      $this->dispatchEvent(ParagraphChanged::class);
      return $block;
   }

   public function removeBlock(Block $block)
   {
      $this->conclusion->ensureCanChange();

      if (!$this->blocks->contains($block)) {
         throw new DomainException("Block with id `{$block->getId()}` does not belongs to paragraph with id "
            . "`{$this->id}`");
      }

      $this->dispatchEvent(BlockDeleted::class, $block);
      $this->dispatchEvent(ParagraphChanged::class);
      $this->blocks->removeElement($block);

      return $this;
   }

   public function getConclusion(): Conclusion
   {
      return $this->conclusion;
   }

   public function getId(): Id
   {
      return $this->id;
   }

   public function getTitle(): Title
   {
      return $this->title;
   }

   public function setTitle(Title $title): self
   {
      $this->conclusion->ensureCanChange();

      $this->title = $title;
      $this->dispatchEvent(ParagraphChanged::class);
      return $this;
   }

   public function getOrder(): Order
   {
      return $this->order ?: Order::initial();
   }

   public function setOrder(Order $order): self
   {
      $this->conclusion->ensureCanChange();

      $this->order = $order;
      $this->dispatchEvent(ParagraphChanged::class);
      return $this;
   }

   public function addCertificate(Certificate $certificate)
   {
      $this->conclusion->ensureCanChange();

      if (!$this->certificates->contains($certificate)) {
         $this->certificates->add($certificate);
         $this->dispatchEvent(ParagraphChanged::class);
      }
      return $this;
   }

   /**
    * @return Certificate[]|ArrayCollection
    */
   public function getCertificates()
   {
      return $this->certificates;
   }

   public function removeCertificates()
   {
      $this->conclusion->ensureCanChange();

      if (!$this->certificates->isEmpty()) {
         $this->certificates->clear();
         $this->dispatchEvent(ParagraphChanged::class);
      }
      return $this;
   }

   private function dispatchEvent(string $class, $arg = null)
   {
      DomainEventDispatcher::instance()->dispatch(new $class($arg ?? $this));
   }
}
