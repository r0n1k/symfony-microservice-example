<?php


namespace App\Domain\Template\Entity\TemplateParagraph\TemplateDictionary;


use App\Domain\Template\Entity\TemplateParagraph\TemplateParagraph;
use Doctrine\ORM\Mapping as ORM;
use LogicException;
use Webmozart\Assert\Assert;

/**
 * Class TemplateDictionary
 * @package App\Domain\Template\Entity\TemplateParagraph\TemplateDictionary
 *
 * @ORM\Entity()
 * @ORM\Table(name="template_dictionaries", uniqueConstraints={
 *    @ORM\UniqueConstraint(columns={
 *       "paragraph_id",
 *       "dictionary_key",
 *    })
 * })
 * @ORM\HasLifecycleCallbacks()
 */
class TemplateDictionary
{

   /**
    * @var int
    * @ORM\Id()
    * @ORM\Column(type="integer")
    * @ORM\GeneratedValue(strategy="SEQUENCE")
    * @ORM\SequenceGenerator(sequenceName="conclusion_template_dictionary_id_seq")
    */
   protected ?int $id;

   /**
    * @ORM\ManyToOne(
    *    targetEntity="\App\Domain\Template\Entity\TemplateParagraph\TemplateParagraph",
    *    inversedBy="dictionaries"
    * )
    * @ORM\JoinColumn(nullable=false, fieldName="paragraph_id")
    */
   protected TemplateParagraph $paragraph;

   /**
    * @ORM\Column(nullable=false, type="string", name="dictionary_key")
    * @var string
    */
   protected string $dictionaryKey;

   public function __construct(string $dictionaryKey, TemplateParagraph $paragraph)
   {
      $this->dictionaryKey = $dictionaryKey;
      $this->paragraph = $paragraph;
   }

   /**
    * @return string
    */
   public function getDictionaryKey(): string
   {
      return $this->dictionaryKey;
   }

   /**
    * @return TemplateParagraph
    */
   public function getParagraph(): TemplateParagraph
   {
      return $this->paragraph;
   }

   /**
    * @return int
    */
   public function getId(): ?int
   {
      return $this->id;
   }

   /**
    * @ORM\PrePersist()
    * @noinspection PhpUnused
    */
   public function errorOnPersistIntoBasicTemplate()
   {
      if ($this->paragraph->getTemplate()->getIsBasic() !== false) {
         throw new LogicException('Cannot persist certificate into basic template');
      }
   }
}
