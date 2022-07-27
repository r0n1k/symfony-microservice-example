<?php


namespace App\Domain\Project\Entity\Conclusion\Pdf\Signature;


use App\Domain\Common\DomainEventDispatcher;
use App\Domain\Project\Entity\Conclusion\Pdf\Pdf;
use App\Domain\Project\Event\Conclusion\ConclusionChanged;
use App\Domain\Project\Event\Conclusion\Pdf\Signature\SignatureCreated;
use Doctrine\ORM\Mapping as ORM;

/**
 * Class Signature
 * @package App\Domain\Project\Entity\Conclusion\Pdf
 *
 * @ORM\Entity()
 * @ORM\Table(name="conclusion_pdf_signature")
 */
class Signature
{
   /**
    * @var int
    * @ORM\Id()
    * @ORM\GeneratedValue(strategy="SEQUENCE")
    * @ORM\SequenceGenerator(sequenceName="conclusion_pdf_signature_id_seq")
    * @ORM\Column(type="bigint")
    */
   protected int $id;

   /**
    * @var Pdf
    * @ORM\ManyToOne(targetEntity="App\Domain\Project\Entity\Conclusion\Pdf\Pdf",
    *    inversedBy="signatures",
    *    cascade={"persist", "remove"})
    * @ORM\JoinColumn(onDelete="CASCADE")
    */
   protected Pdf $pdf;

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
     * @var array
     * @ORM\Column(type="array")
     */
    protected array $data;

   public function __construct(Pdf $pdf, string $path, array $data)
   {
      $this->pdf = $pdf;
      $this->path = $path;
      $this->data = $data;
      $this->dispatchEvent(ConclusionChanged::class, $this->pdf->getConclusion());
   }

   public function getPath() {
      return $this->path;
   }

   public function getPdf() {
      return $this->pdf;
   }

    public function getData(): array
    {
        return $this->data;
    }

    public function setData(array $data): void
    {
        $this->data = $data;
    }

    public function getId(): int
    {
        return $this->id;
    }

    private function dispatchEvent(string $class, $arg = null)
    {
        DomainEventDispatcher::instance()->dispatch(new $class($arg ?? $this));
    }

}
