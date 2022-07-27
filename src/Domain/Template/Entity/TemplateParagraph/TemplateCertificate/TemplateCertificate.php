<?php


namespace App\Domain\Template\Entity\TemplateParagraph\TemplateCertificate;


use App\Domain\Template\Entity\TemplateParagraph\TemplateParagraph;
use Doctrine\ORM\Mapping as ORM;
use Webmozart\Assert\Assert;

/**
 * Class TemplateCertificate
 * @package App\Domain\Template\Entity\TemplateParagraph\TemplateCertificate
 *
 * @ORM\Entity()
 * @ORM\HasLifecycleCallbacks()
 */
class TemplateCertificate
{

   /**
    * @var int
    * @ORM\Id()
    * @ORM\GeneratedValue(strategy="SEQUENCE")
    * @ORM\SequenceGenerator(sequenceName="conclusion_template_certificate_id_seq")
    * @ORM\Column(type="bigint")
    */
   private ?int $id = null;


   /**
    * @var TemplateParagraph
    * @ORM\ManyToOne(
    *    targetEntity="\App\Domain\Template\Entity\TemplateParagraph\TemplateParagraph",
    *    inversedBy="certificates",
    * )
    * @ORM\JoinColumn(nullable=false)
    */
   private TemplateParagraph $paragraph;

   /**
    * @var string
    * @ORM\Column(type="string", nullable=false)
    */
   private string $name;


   public function __construct(string $name, TemplateParagraph $paragraph)
   {
      $this->name = $name;
      $this->paragraph = $paragraph;
   }


   public function getParagraph(): TemplateParagraph
   {
      return $this->paragraph;
   }

   /**
    * @return int
    */
   public function getId(): int
   {
      return $this->id;
   }

   /**
    * @return string
    */
   public function getName(): string
   {
      return $this->name;
   }

   /**
    * @param string $name
    * @return TemplateCertificate
    */
   public function setName(string $name): self
   {
      $this->name = $name;

      return $this;
   }

   /**
    * @ORM\PrePersist()
    * @noinspection PhpUnused
    */
   public function errorOnPersistIntoBasicTemplate()
   {
      Assert::false($this->paragraph->getTemplate()->getIsBasic(), 'Cannot persist certificate into basic template');
   }
}
