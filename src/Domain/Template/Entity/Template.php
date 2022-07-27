<?php


namespace App\Domain\Template\Entity;


use App\Domain\Template\Entity\TemplateParagraph\TemplateBlockKind;
use App\Domain\Template\Entity\TemplateParagraph\Id as ParagraphId;
use App\Domain\Template\Entity\TemplateParagraph\TemplateParagraph;
use App\Domain\Template\Entity\TemplateParagraph\Title as ParagraphTitle;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use LogicException;
use OpenApi\Annotations as OA;

/**
 * Class Template
 * @package App\Domain\Entity\Template
 *
 * @ORM\Entity()
 * @ORM\HasLifecycleCallbacks()
 */
class Template
{
   /**
    * @OA\Schema(schema="TemplateId", type="string", format="uuid")
    * @ORM\Id()
    * @ORM\Column(type="template_id", unique=true)
    * @ORM\GeneratedValue(strategy="NONE")
    */
   protected Id $id;

   /**
    * @var Title
    * @ORM\Column(type="template_title")
    */
   protected Title $title;

   /**
    * @var TemplateParagraph[]|ArrayCollection
    * @ORM\OneToMany(
    *    targetEntity="\App\Domain\Template\Entity\TemplateParagraph\TemplateParagraph",
    *    mappedBy="template",
    *    cascade={"all"},
    *  )
    */
   protected $paragraphs;

   /**
    * Является ли базовым шаблоном, который хранится в файле
    * @var bool
    */
   protected bool $isBasic = false;

   public function __construct(Id $id, Title $title, bool $isBasic)
   {
      $this->paragraphs = new ArrayCollection();
      $this->id = $id;
      $this->title = $title;
      $this->isBasic = $isBasic;
   }

   public function setIsBasic(bool $isBasic): self
   {
      $this->isBasic = $isBasic;
      return $this;
   }

   /**
    * @ORM\PrePersist()
    * @noinspection PhpUnused
    */
   public function errorOnIsBasicPrePersist()
   {
      if ($this->isBasic) {
         throw new LogicException('Cannot persist basic template');
      }
   }

   public function getId()
   {
      return $this->id;
   }

   public function getTitle(): Title
   {
      return $this->title;
   }

   public function setTitle(Title $title): self
   {
      $this->title = $title;

      return $this;
   }

   /**
    * @return Collection|TemplateParagraph[]
    */
   public function getParagraphs(): Collection
   {
      return $this->paragraphs;
   }

   public function getIsBasic()
   {
      return $this->isBasic;
   }

   /**
    * @return ArrayCollection|TemplateParagraph[]
    */
   public function getRootParagraphs(): ArrayCollection
   {
      return new ArrayCollection($this->getParagraphs()->filter(static function (TemplateParagraph $paragraph) {
         return $paragraph->getParent() === null;
      })->getValues());
   }

   public function addParagraph(ParagraphTitle $title,
                                ?ParagraphId $id = null,
                                ?TemplateBlockKind $blockKind = null): TemplateParagraph {

      $paragraph = new TemplateParagraph($this, $title, $id, $blockKind);

      if (!$this->paragraphs->contains($paragraph)) {
         $this->paragraphs->add($paragraph);
      }

      return $paragraph;
   }
}
