<?php


namespace App\Domain\Template\Entity\TemplateParagraph;


use App\Domain\Template\Entity\Template;
use App\Domain\Template\Entity\TemplateParagraph\TemplateCertificate\TemplateCertificate;
use App\Domain\Template\Entity\TemplateParagraph\TemplateDictionary\TemplateDictionary;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use DomainException;
use LogicException;
use OpenApi\Annotations as OA;
use Webmozart\Assert\Assert;

/**
 * Class Paragraph
 *
 * @ORM\Entity()
 * @ORM\Table(name="template_paragraph")
 * @ORM\HasLifecycleCallbacks()
 */
class TemplateParagraph
{

   /**
    * @ORM\Id()
    * @ORM\Column(type="template_paragraph_id")
    * @ORM\GeneratedValue(strategy="NONE")
    * @ORM\SequenceGenerator(sequenceName="template_paragraph_id_seq", initialValue=1)
    * @OA\Schema(schema="TemplateParagraphId", type="integer")
    */
   protected ?Id $id;

   /**
    * @ORM\Column(type="template_paragraph_title")
    * @var Title
    */
   protected Title $title;

   /**
    * @ORM\Column(type="template_paragraph_blockkind", nullable=true)
    * @var TemplateBlockKind
    */
   protected ?TemplateBlockKind $blockKind;

   /**
    * @var self
    * @ORM\ManyToOne(targetEntity="TemplateParagraph", inversedBy="children", cascade={"persist"})
    * @ORM\JoinColumn(name="parent_id", referencedColumnName="id")
    */
   protected ?TemplateParagraph $parent;

   /**
    * @ORM\OneToMany(targetEntity="TemplateParagraph", mappedBy="parent", cascade={"all"})
    * @var self[]|ArrayCollection
    */
   protected $children;

   /**
    * @ORM\ManyToOne(targetEntity="\App\Domain\Template\Entity\Template", inversedBy="paragraphs", cascade={"all"})
    * @var Template
    */
   protected Template $template;

   /**
    * @ORM\OneToMany(
    *    targetEntity="\App\Domain\Template\Entity\TemplateParagraph\TemplateCertificate\TemplateCertificate",
    *    mappedBy="paragraph",
    *    cascade={"all"},
    * )
    * @var ArrayCollection|TemplateCertificate[]
    */
   protected $certificates;

   /**
    * @ORM\OneToMany(
    *    targetEntity="\App\Domain\Template\Entity\TemplateParagraph\TemplateDictionary\TemplateDictionary",
    *    mappedBy="paragraph",
    *    cascade={"all"},
    * )
    * @var ArrayCollection|TemplateDictionary[]
    */
   protected $dictionaries;

   /**
    * @var int
    * @ORM\Column(type="integer", nullable=false, name="sort_order", options={"default": 0})
    */
   protected $order = 0;

   public function __construct(Template $template,
                               Title $title,
                               ?Id $id,
                               ?TemplateBlockKind $blockKind = null,
                               ?TemplateParagraph $parent = null)
   {
      Assert::true($template->getIsBasic() || ($id instanceof Id), 'Template paragraph should be basic or have an Id');
      $this->children = new ArrayCollection();
      $this->template = $template;
      $this->title = $title;
      $this->id = $id;
      $this->blockKind = $blockKind;
      $this->parent = $parent;
      $this->certificates = new ArrayCollection();
      $this->dictionaries = new ArrayCollection();
   }

   /**
    * @ORM\PrePersist()
    * @noinspection PhpUnused
    */
   public function errorOnIsBasicPrePersist()
   {
      if ($this->template->getIsBasic()) {
         throw new LogicException('Cannot persist basic template paragraph');
      }
   }

   public function getId(): Id
   {
      return $this->id;
   }

   public function getParent(): ?self
   {
      return $this->parent;
   }

   public function setParent(?self $parent): self
   {
      $this->parent = $parent;

      return $this;
   }

   /**
    * @return Collection|TemplateParagraph[]
    */
   public function getChildren(): Collection
   {
      return $this->children;
   }

   public function getTemplate(): Template
   {
      return $this->template;
   }

   public function getTitle(): Title
   {
      return $this->title;
   }

   public function getBlockKind(): ?TemplateBlockKind
   {
      return $this->blockKind;
   }

   public function addChild(Title $title, ?Id $id = null, ?TemplateBlockKind $kind = null): self
   {
      $child = new TemplateParagraph($this->template, $title, $id, $kind, $this);
      $this->children->add($child);

      return $child;
   }

   public function addCertificate(string $name): TemplateCertificate
   {
      if ($this->getCertificateByName($name) !== null) {
         throw new DomainException("Certificate with name `$name` already exists on paragraph");
      }

      $certificate = new TemplateCertificate($name, $this);
      $this->certificates->add($certificate);

      return $certificate;
   }

   public function removeCertificateByName(string $name): self
   {
      $certificate = $this->getCertificateByName($name);
      if ($certificate === null) {
         throw new DomainException("Certificate with name `$name` does not exists on paragraph");
      }

      $this->certificates->removeElement($certificate);

      return $this;
   }

   public function getCertificateByName(string $name): ?TemplateCertificate
   {
      return $this->certificates->filter(static function (TemplateCertificate $certificate) use ($name) {
         return $certificate->getName() === $name;
      })->first() ?: null;
   }

   /**
    * @return ArrayCollection|TemplateCertificate[]
    */
   public function getCertificates()
   {
      return $this->certificates;
   }


   public function addDictionary($key): TemplateDictionary
   {
      if ($this->getDictionary($key) !== null) {
         throw new DomainException("Dictionary with key $key already exists");
      }

      $dictionary = new TemplateDictionary($key, $this);
      $this->dictionaries->add($dictionary);
      return $dictionary;
   }

   public function removeDictionary($key): self
   {
      $dictionary = $this->getDictionary($key);
      if (!$dictionary instanceof TemplateDictionary) {
         throw new DomainException("Dictionary with key $key does not exists");
      }

      $this->dictionaries->removeElement($dictionary);
      return $this;
   }

   public function getDictionary($key)
   {
      return $this->dictionaries->filter(static function (TemplateDictionary $dictionary) use ($key) {
         return $dictionary->getDictionaryKey() === $key;
      })->first() ?: null;
   }

   /**
    * @return TemplateDictionary[]|ArrayCollection
    */
   public function getDictionaries()
   {
      return $this->dictionaries;
   }

   /**
    * @return int
    */
   public function getOrder(): int
   {
      return $this->order;
   }

   /**
    * @param int $order
    * @return TemplateParagraph
    */
   public function setOrder(int $order): self
   {
      $this->order = $order;

      return $this;
   }
}
