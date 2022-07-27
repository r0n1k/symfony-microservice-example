<?php


namespace App\Http\Formatter\Formatters\Conclusion;

use App\Domain\Project\Entity\Conclusion\Conclusion;
use App\Domain\Project\Entity\Users\User\Id;
use App\Domain\Project\Entity\Users\User\User;
use App\Domain\Project\Repository\Certificate\CertificateRepository;
use App\Domain\Project\Repository\Conclusion\ConclusionRepository;
use App\Domain\Project\Repository\Conclusion\Paragraph\ParagraphRepository;
use App\Domain\Project\Repository\Users\ProjectUserAssignment\ProjectUserAssignmentRepository;
use App\Domain\Project\Repository\Users\User\UserRepository;
use App\Domain\Template\Repository\TemplateRepository;
use App\Http\Formatter\Base\EntityFormatter;
use App\Http\Formatter\Base\FormatEvent;
use App\Http\ReadModel\ConclusionDictionariesFetcher;
use OpenApi\Annotations as OA;
use Symfony\Component\Security\Core\Security;


/**
 * @noinspection PhpUnused
 */

class ConclusionFormatter extends EntityFormatter
{

    /**
     * @var ParagraphRepository
     */
    private ParagraphRepository $paragraphs;
    /**
     * @var ConclusionDictionariesFetcher
     */
    private ConclusionDictionariesFetcher $dictionaries;
    /**
     * @var CertificateRepository
     */
    private CertificateRepository $certificates;
    private ?User $authorizedUser;
    /**
     * @var ProjectUserAssignmentRepository
     */
    private ProjectUserAssignmentRepository $assignments;
    /**
     * @var ConclusionRepository
     */
    private ConclusionRepository $conclusions;
    /**
     * @var TemplateRepository
     */
    private TemplateRepository $templates;
    /**
     * @var UserRepository
     */
    private UserRepository $users;


    public function __construct(
        ParagraphRepository $paragraphs,
        ConclusionDictionariesFetcher $dictionaries,
        CertificateRepository $certificates,
        Security $security,
        ProjectUserAssignmentRepository $assignments,
        ConclusionRepository $conclusions,
        TemplateRepository $templates,
        UserRepository $users
    )
    {
        $this->paragraphs = $paragraphs;
        $this->dictionaries = $dictionaries;
        $this->certificates = $certificates;
        $this->assignments = $assignments;
        $this->conclusions = $conclusions;
        $this->templates = $templates;
        $this->users = $users;

        $this->ensureAuthorizedUser($security);
    }

    /**
     * @param Conclusion $conclusion
     * @return array
     */
    public function format($conclusion)
    {

        $latestConclusion = $this->conclusions->findLatestForProject($conclusion->getProject());
        if ($latestConclusion instanceof Conclusion) {
            $latestRevisionNum = $latestConclusion->getRevision()->getValue();
        } else {
            $latestRevisionNum = null;
        }

        if ($conclusion->getTemplateId()) {
            $template = $this->templates->find($conclusion->getTemplateId()->getValue());
        } else {
            $template = null;
        }

        $projectUsers = [];
        if ($this->authorizedUser && !$this->authorizedUser->getRole()->isClient()) {
            $projectUsers = $conclusion->getProject()->getUsers();
        }


        /**
         * @OA\Schema(schema="Conclusion", type="object",
         *   @OA\Property(property="id", type="string", format="uuid"),
         *   @OA\Property(property="revision", type="integer"),
         *   @OA\Property(property="paragraphs", type="array", @OA\Items(ref="#/components/schemas/ConclusionParagraph")),
         *   @OA\Property(property="author", ref="#/components/schemas/User"),
         *   @OA\Property(property="current_user_id", type="integer"),
         *   @OA\Property(property="project_users", type="array", @OA\Items(ref="#/components/schemas/User")),
         *   @OA\Property(property="dictionaries", type="array", @OA\Items(ref="#/components/schemas/Dictionary")),
         *   @OA\Property(property="name", type="string"),
         *   @OA\Property(property="kind", ref="#/components/schemas/ConclusionKind"),
         *   @OA\Property(property="is_local", type="bool"),
         *   @OA\Property(property="project_id", type="string", format="uuid"),
         *   @OA\Property(property="created_at", type="integer"),
         *   @OA\Property(property="is_accessible_to_client", type="boolean"),
         *   @OA\Property(property="file_type_state", ref="#/components/schemas/ConclusionFileTypeState"),
         *   @OA\Property(property="comment", type="string"),
         * )
         */
        return [
            'id' => $conclusion->getId(),
            'template_id' => $conclusion->getTemplateId(),
            'project_id' => ($p = $conclusion->getProject()) ? $p->getId() : null,
            'revision' => ($r = $conclusion->getRevision()) ? $r->getValue() : null,
            'paragraphs' => $this->paragraphs->findAllByConclusion($conclusion),
            'dictionaries' => $this->dictionaries->fetch($conclusion),
            'author' => $conclusion->getAuthor(),
            'current_user_id' => ($u = $this->authorizedUser) ? $u->getId()->getValue() : null,
            'project_users' => $projectUsers,
            'name' => ($t = $conclusion->getTitle()) ? $t->getValue() : '',
            'kind' => (string)$conclusion->getKind(),
            'is_local' => $conclusion->getIsLocal() ?? false,
            'certificates' => $this->certificates->all(),
            'created_at' => ($c = $conclusion->getCreatedAt()) ? $c->getTimestamp() : null,
            'latest_revision' => $latestRevisionNum,
            'template' => $template,
            'state' => (string)$conclusion->getState(),
            'print_form_key' => $conclusion->getPrintFormKey(),
            'pdfs' => $conclusion->getPdfs(),
            'is_accessible_to_client' => $conclusion->getIsAccessibleToClient(),
            'file_type_state' => ($s = $conclusion->getFileTypeState()) ? $s->getValue() : null,
            'comment' => $conclusion->getComment(),
        ];
    }

    /**
     * @inheritDoc
     */
    protected function supports(FormatEvent $event): bool
    {
        return $event->getFormattableData() instanceof Conclusion;
    }

    private function ensureAuthorizedUser($security)
    {
        $this->authorizedUser = $this->users->find(Id::of($security->getUser()->getId())) ?? null;
    }
}
