<?php


namespace App\Domain\Project\Entity\Conclusion\Pdf;


use App\Domain\Common\DomainEventDispatcher;
use App\Domain\Project\Entity\Conclusion\Conclusion;
use App\Domain\Project\Entity\Conclusion\Pdf\Signature\Signature;
use App\Domain\Project\Event\Conclusion\ConclusionChanged;
use DateTimeImmutable;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

/**
 * Class Pdf
 * @package App\Domain\Project\Entity\Conclusion\Pdf
 *
 * @ORM\Entity()
 * @ORM\Table(name="conclusion_pdf")
 */
class Pdf
{

   /**
    * @var int
    * @ORM\Id()
    * @ORM\GeneratedValue(strategy="SEQUENCE")
    * @ORM\SequenceGenerator(sequenceName="conclusion_pdf_id_seq")
    * @ORM\Column(type="bigint")
    */
   protected int $id;

   /**
    * @var string
    * @ORM\Column(type="string")
    */
   protected string $path;

    /**
     * @var string
     * @ORM\Column(type="string", nullable=true)
     */
    protected ?string $fileName = null;

    /**
     * @var string
     * @ORM\Column(type="string", nullable=false, options={"default": "default"})
     */
    protected string $state = 'default';

    /**
     * @var DateTimeImmutable
     * @ORM\Column(type="datetime_immutable")
     */
    protected DateTimeImmutable $createdAt;

   /**
    * @var Conclusion
    * @ORM\ManyToOne(targetEntity="App\Domain\Project\Entity\Conclusion\Conclusion", inversedBy="pdfs")
    */
   protected Conclusion $conclusion;

   /**
    * @var Collection|Signature[]
    * @ORM\OneToMany(targetEntity="App\Domain\Project\Entity\Conclusion\Pdf\Signature\Signature", mappedBy="pdf")
    */
   protected Collection $signatures;

   public function __construct(Conclusion $conclusion, string $path, ?string $fileName = null)
   {
      $this->path = $path;
      $this->conclusion = $conclusion;
      $this->signatures = new ArrayCollection();
      $this->createdAt = new DateTimeImmutable();
      $this->fileName = $fileName;
   }

   public function addSignature($path) {
      $signature = new Signature($this, $path, []);
      $this->signatures->add($signature);
   }

   public function removeSignature($id) {
      $signature = $this->signatures->filter(static function ($signature) use ($id) {
         return $signature->getId() === $id;
      })->first() ?: null;

      if (!$signature) {
         throw new \DomainException("Signature with id {$id} is not found into pdf");
      }

      $this->signatures->removeElement($signature);

      return $this;
   }

    public function getId(): int
    {
        return $this->id;
    }

    public function getConclusion(): Conclusion
    {
        return $this->conclusion;
    }

    public function getPath(): string
    {
        return $this->path;
    }

    public function setPath(string $path): void
    {
        $this->path = $path;
    }

    public function getCreatedAt(): DateTimeImmutable
    {
        return $this->createdAt;
    }

    /**
     * @return Signature[]|Collection
     */
    public function getSignatures()
    {
        return $this->signatures;
    }

    /**
     * @return string|null
     */
    public function getFileName(): ?string
    {
        return $this->fileName;
    }

    /**
     * @return string
     */
    public function getState(): string
    {
        return $this->state;
    }

    /**
     * @param string $state
     */
    public function setState(string $state): void
    {
        $this->state = $state;
        DomainEventDispatcher::instance()->dispatch(new ConclusionChanged($this->getConclusion()));
    }

}
