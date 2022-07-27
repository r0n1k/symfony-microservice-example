<?php


namespace App\Domain\Project\Entity\Conclusion;

use App\Domain\Common\DomainEventDispatcher;
use App\Domain\Project\Entity\Conclusion\Pdf\Pdf;
use App\Domain\Project\Event\Conclusion\ConclusionCreated;
use App\Domain\Project\Entity\Conclusion\Paragraph\Id as ParagraphId;
use App\Domain\Project\Entity\Conclusion\Paragraph\Order;
use App\Domain\Project\Entity\Conclusion\Paragraph\Paragraph;
use App\Domain\Project\Entity\Conclusion\Paragraph\Title as ParagraphTitle;
use App\Domain\Project\Entity\Users\User\User;
use App\Domain\Project\Event\Conclusion\ConclusionChanged;
use App\Domain\Project\Event\Conclusion\Paragraph\ParagraphDeleted;
use DateTimeImmutable;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use App\Domain\Project\Entity\Project\Project;
use DomainException;
use OpenApi\Annotations as OA;

/**
 * Class Conclusion
 *
 * @package App\Domain\Entity
 *
 * @ORM\Entity()
 * @ORM\Table(name="conclusions")
 */
class Conclusion
{

    /**
     * @OA\Schema(schema="ConclusionId", type="string", format="uuid")
     * @ORM\Id()
     * @ORM\Column(type="conclusion_id")
     * @ORM\GeneratedValue(strategy="NONE")
     * @var Id
     */
    protected Id $id;

    /**
     * @ORM\OneToMany(
     *    targetEntity="\App\Domain\Project\Entity\Conclusion\Paragraph\Paragraph",
     *    mappedBy="conclusion",
     *    cascade={"persist", "refresh", "remove"},
     *    fetch="EAGER"
     * )
     * @var Paragraph[]|ArrayCollection
     */
    protected $paragraphs;

    /**
     * @var Pdf[]|ArrayCollection
     * @ORM\OneToMany(
     *    targetEntity="App\Domain\Project\Entity\Conclusion\Pdf\Pdf",
     *    mappedBy="conclusion",
     *    cascade={"persist", "refresh", "remove"},
     *    fetch="EAGER"
     *  )
     */
    protected $pdfs;

    /**
     * @ORM\ManyToOne(targetEntity="\App\Domain\Project\Entity\Project\Project")
     * @ORM\JoinColumn(name="project_id", referencedColumnName="id", nullable=false)
     * @var Project
     */
    protected Project $project;

    /**
     * @ORM\Column(type="conclusion_kind")
     * @var Kind
     */
    protected Kind $kind;

    /**
     * @ORM\Column(type="conclusion_revision", name="revision")
     * @var Revision
     */
    protected Revision $revision;

    /**
     * @var TemplateId|null
     * @ORM\Column(type="conclusion_template_id", nullable=true)
     */
    protected ?TemplateId $templateId = null;

    /**
     * @var User
     * @ORM\ManyToOne(
     *    targetEntity="\App\Domain\Project\Entity\Users\User\User",
     *    inversedBy="conclusions",
     *    cascade={"persist", "refresh"}
     *  )
     */
    protected User $author;

    /**
     * @var Title
     * @ORM\Column(type="conclusion_title")
     */
    protected Title $title;

    /**
     * @var DateTimeImmutable
     * @ORM\Column(type="datetime_immutable")
     */
    protected DateTimeImmutable $createdAt;

    /**
     * @var State
     * @ORM\Column(type="conclusion_state", options={"default": "default"})
     */
    protected State $state;

    /**
     * @var bool Доступно ли заявителю
     * @ORM\Column(type="boolean", options={"default": false})
     */
    protected bool $accessibleToClient = false;

    /**
     * @var string
     * @ORM\Column(type="string", nullable=true)
     */
    protected ?string $printFormKey = null;

    /**
     * @var string
     * @ORM\Column(type="string", nullable=true)
     */
    protected ?string $pdfPath = null;

    /**
     * @var string
     * @ORM\Column(type="text", nullable=true)
     */
    protected ?string $comment = null;

    /**
     * @ORM\Column(type="conclusion_file_type_state", nullable=true)
     */
    protected ?FileTypeState $fileTypeState = null;


    /**
     * @var boolean
     * @ORM\Column(type="boolean", nullable=true)
     */
    protected ?bool $isLocal = null;

    /**
     * @var int
     * @ORM\Column(type="integer", nullable=true)
     */
    protected ?int $oldId;


    public function __construct(
        Id $id,
        Title $title,
        User $author,
        Project $project,
        Kind $kind,
        Revision $revision,
        ?TemplateId $templateId = null,
        ?State $state = null,
        ?bool $isLocal = null
    ) {
        $this->id = $id;
        $this->title = $title;
        $this->author = $author;
        $this->paragraphs = new ArrayCollection();
        $this->createdAt = new DateTimeImmutable();
        $this->project = $project;
        $this->kind = $kind;
        $this->templateId = $templateId;
        $this->revision = $revision;
        $this->state = $state ?? State::default();
        $this->isLocal = $isLocal ?? false;
        $this->dispatchEvent(ConclusionCreated::class);
    }

    public function getId()
    {
        return $this->id;
    }

    public function getKind(): ?Kind
    {
        return $this->kind;
    }

    public function getRevision(): Revision
    {
        return $this->revision;
    }

    /**
     * @return Collection|Paragraph[]
     */
    public function getParagraphs(): Collection
    {
        $iterator = $this->paragraphs->getIterator();
        $iterator->uasort(
            static function (Paragraph $a, Paragraph $b) {
                if ($a->getOrder()->equals($b->getOrder())) {
                    return $a->getId()->getValue() > $b->getId()->getValue() ? 1 : -1;
                }
                return $a->getOrder()->greaterThen($b->getOrder()) ? 1 : -1;
            }
        );
        return new ArrayCollection(iterator_to_array($iterator));
    }


    public function getProject(): Project
    {
        return $this->project;
    }


    public function getCreatedAt(): DateTimeImmutable
    {
        return $this->createdAt;
    }


    public function getAuthor(): ?User
    {
        return $this->author;
    }

    public function getTemplateId(): ?TemplateId
    {
        return $this->templateId;
    }

    public function setTemplateId(TemplateId $templateId, bool $ensureCanChange = true): self
    {
        if ($ensureCanChange) {
            $this->ensureCanChange();
        }

        $this->templateId = $templateId;
        return $this;
    }

    public function getPrintFormKey(): ?string
    {
        return $this->printFormKey;
    }

    public function setPrintFormKey(string $key): void
    {
        $this->printFormKey = $key;
    }

    public function getPdfPath(): ?string
    {
        return $this->pdfPath;
    }

    public function setPdfPath(string $path): void
    {
        $this->pdfPath = $path;
    }

    /**
     * @return ArrayCollection|Paragraph[]
     */
    public function getRootParagraphs()
    {
        return new ArrayCollection(
            $this->getParagraphs()->filter(
                static function (Paragraph $paragraph) {
                    return $paragraph->getParent() === null;
                }
            )->getValues()
        );
    }

    public function getTitle(): Title
    {
        return $this->title;
    }

    public function setTitle(Title $title): self
    {
        $this->ensureCanChange();

        $this->title = $title;
        $this->dispatchEvent(ConclusionChanged::class);

        return $this;
    }

    public function addParagraph(ParagraphId $id, ParagraphTitle $title, Order $order = null): Paragraph
    {
        $this->ensureCanChange();

        $paragraph = new Paragraph($id, $this, $title, $order);
        $this->paragraphs->add($paragraph);
        return $paragraph;
    }

    public function removeParagraph(ParagraphId $id)
    {
        $this->ensureCanChange();

        foreach ($this->paragraphs as $paragraph) {
            if ($paragraph->getId()->isEqual($id)) {
                $this->paragraphs->removeElement($paragraph);
                $this->dispatchEvent(ParagraphDeleted::class, $paragraph);
                return;
            }
        }
        throw new DomainException('Paragraph not found');
    }


    /**
     * @return Collection|Pdf[]
     * @throws \Exception
     */
    public function getPdfs(): Collection
    {
        $pdfs = empty($this->pdfs) ? [] : $this->pdfs->getValues();
        return new ArrayCollection($pdfs);
    }

    public function addPdf(string $path, ?string $fileName = null): Pdf
    {
        $this->ensureNotDeleted();

        $pdf = new Pdf($this, $path, $fileName);
        $this->pdfs->add($pdf);
        $this->dispatchEvent(ConclusionChanged::class);
        return $pdf;
    }

    public function removePdf(int $id)
    {
        $this->ensureNotDeleted();

        foreach ($this->pdfs as $pdf) {
            if ($pdf->getId() == $id) {
                $this->pdfs->removeElement($pdf);
                $this->dispatchEvent(ConclusionChanged::class);
                return;
            }
        }
        throw new DomainException('Pdf not found');
    }


    public function getState()
    {
        return $this->state;
    }

    public function setState(State $state): self
    {
        $this->state = $state;
        $this->dispatchEvent(ConclusionChanged::class);
        return $this;
    }


    public function getIsAccessibleToClient(): bool
    {
        return $this->accessibleToClient;
    }

    public function setIsAccessibleToClient(bool $accessible): self
    {
        $this->accessibleToClient = $accessible;
        $this->dispatchEvent(ConclusionChanged::class);
        return $this;
    }


    public function getIsLocal(): ?bool
    {
        return $this->isLocal;
    }


    public function getComment(): ?string
    {
        return $this->comment;
    }

    public function setComment(?string $comment): self
    {
        $this->ensureCanChange();

        $this->comment = $comment;
        $this->dispatchEvent(ConclusionChanged::class);

        return $this;
    }


    public function getFileTypeState(): ?FileTypeState
    {
        return $this->fileTypeState;
    }

    public function setFileTypeState(?FileTypeState $fileTypeState): self
    {
        $this->ensureCanChange();

        $this->fileTypeState = $fileTypeState;
        $this->dispatchEvent(ConclusionChanged::class);

        return $this;
    }


    public function clear()
    {
        $this->ensureCanChange();
        $this->paragraphs->clear();

        return $this;
    }


    private function dispatchEvent(string $class, $arg = null)
    {
        DomainEventDispatcher::instance()->dispatch(new $class($arg ?? $this));
    }


    public function ensureCanChange()
    {
        if ($this->state->isLocked()) {
            throw new DomainException('Заключение заблокировано');
        }

        $this->ensureNotDeleted();
    }

    public function ensureNotDeleted()
    {
        if ($this->state->isRemoved()) {
            throw new DomainException('Заключение удалено');
        }
    }
}
