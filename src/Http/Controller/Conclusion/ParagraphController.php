<?php


namespace App\Http\Controller\Conclusion;

use App\Domain\Project\Entity\Conclusion\Conclusion;
use App\Domain\Project\Entity\Conclusion\Paragraph\Id;
use App\Domain\Project\Entity\Conclusion\Paragraph\Paragraph;
use App\Domain\Project\Repository\Conclusion\Paragraph\ParagraphRepository;
use App\Domain\Project\UseCase\Conclusion\Paragraph\Create;
use App\Domain\Project\UseCase\Conclusion\Paragraph\Delete;
use App\Domain\Project\UseCase\Conclusion\Paragraph\Rename;
use App\Domain\Project\UseCase\Conclusion\Paragraph\SetParent;
use App\Domain\Project\UseCase\Conclusion\Paragraph\SetCertificates;
use App\Http\Controller\ApiController;
use App\Http\Formatter\UnformattedResponse;
use App\Http\Services\DTOBuilder\DTOBuilder;
use App\Services\Authentication\Voter\ConclusionAccess;
use App\Services\Authentication\Voter\ParagraphAccess;
use OpenApi\Annotations as OA;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Entity;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Routing\Annotation\Route;

/**
 * Class ParagraphController
 * @package App\Http\Controller\Conclusion/**
 * @noinspection PhpUnused
 */
class ParagraphController extends ApiController
{

   /**
    * @OA\Post(
    *    path="/conclusion/{conclusion_id}/paragraph",
    *    summary="Добавить параграф",
    *    tags={"Conclusion Paragraph"},
    *
    *    @OA\Parameter(name="conclusion_id", in="path", required=true,
    *       @OA\Schema(ref="#/components/schemas/ConclusionId")
    *    ),
    *
    *    @OA\RequestBody(@OA\JsonContent(ref="#/components/schemas/CreateConclusionParagraphDTO")),
    *
    *    @OA\Response(response="201", description="ok", @OA\JsonContent(allOf={
    *       @OA\Schema(ref="#/components/schemas/ApiResponse"),
    *       @OA\Schema(type="object", @OA\Property(property="data", ref="#/components/schemas/ConclusionParagraph")),
    *    })),
    *
    *    @OA\Response(response="400", description="Неверные данные",
    *       @OA\JsonContent(ref="#/components/schemas/ApiResponse")
    *    ),
    * )
    *
    * @Route(path="/conclusion/{conclusion_id}/paragraph", methods={"POST"}, name="conclusion.paragraph.create")
    *
    * @Entity("conclusion", options={"id" = "conclusion_id"})
    *
    * @noinspection PhpUnused
    *
    * @param Conclusion $conclusion
    * @param DTOBuilder $builder
    * @param Create\Handler $handler
    * @param ParagraphRepository $paragraphs
    * @return mixed
    */
   public function createParagraph(Conclusion $conclusion,
                                   DTOBuilder $builder,
                                   Create\Handler $handler,
                                   ParagraphRepository $paragraphs)
   {
      /** @var Create\DTO $dto */
      $dto = $builder->buildValidDTO(Create\DTO::class, ['conclusion_id'], ['conclusion_id' => $conclusion->getId()]);

      if ($dto->parent_id) {
         $parent = $paragraphs->get(Id::of($dto->parent_id));
         $this->denyAccessUnlessGranted(ParagraphAccess::EDIT_STRUCTURE, $parent);
      } else {
         $this->denyAccessUnlessGranted(ConclusionAccess::EDIT_STRUCTURE, $conclusion);
      }

      return new UnformattedResponse($handler->handle($dto), 201);
   }

   /**
    * @OA\Patch(
    *    path="/conclusion/{conclusion_id}/paragraph/{paragraph_id}/name",
    *    summary="Переименовать параграф",
    *    tags={"Conclusion Paragraph"},
    *
    *    @OA\Parameter(name="conclusion_id", in="path", required=true,
    *       @OA\Schema(ref="#/components/schemas/ConclusionId")
    *    ),
    *    @OA\Parameter(name="paragraph_id", in="path", required=true,
    *       @OA\Schema(type="integer")
    *    ),
    *
    *    @OA\RequestBody(@OA\JsonContent(ref="#/components/schemas/RenameParagraphDTO")),
    *
    *    @OA\Response(response="200", description="ok", @OA\JsonContent(allOf={
    *       @OA\Schema(ref="#/components/schemas/ApiResponse"),
    *       @OA\Schema(type="object", @OA\Property(property="data", ref="#/components/schemas/ConclusionParagraph")),
    *    })),
    * )
    *
    * @Route(
    *    path="/conclusion/{conclusion_id}/paragraph/{paragraph_id}/name",
    *    methods={"PATCH"},
    *    name="conclusion.paragraph.rename"
    * )
    *
    * @Entity("conclusion", options={"id" = "conclusion_id"})
    * @Entity("paragraph", options={"id" = "paragraph_id"})
    *
    * @noinspection PhpUnused
    *
    * @param Conclusion $conclusion
    * @param Paragraph $paragraph
    * @param Rename\Handler $handler
    * @param DTOBuilder $builder
    * @return mixed
    */
   public function renameParagraph(Conclusion $conclusion,
                                   Paragraph $paragraph,
                                   Rename\Handler $handler,
                                   DTOBuilder $builder)
   {
      $this->validateParagraphConclusion($paragraph, $conclusion);
      $this->denyAccessUnlessGranted(ParagraphAccess::EDIT_PARAGRAPH, $paragraph);
      /** @var Rename\DTO $dto */
      $dto = $builder->buildValidDTO(Rename\DTO::class, ['paragraph_id'], ['paragraph_id' => $paragraph->getId()->getValue()]);
      return $handler->handler($dto);
   }

   /**
    * @OA\Delete(
    *    path="/conclusion/{conclusion_id}/paragraph/{paragraph_id}",
    *    summary="Удалить параграф",
    *    tags={"Conclusion Paragraph"},
    *
    *    @OA\Parameter(name="conclusion_id", in="path", required=true,
    *       @OA\Schema(ref="#/components/schemas/ConclusionId")
    *    ),
    *    @OA\Parameter(name="paragraph_id", in="path", required=true,
    *       @OA\Schema(type="integer")
    *    ),
    *
    *    @OA\Response(response="200", description="ok", @OA\JsonContent(ref="#/components/schemas/ApiResponse")),
    * )
    *
    * @Route(
    *    path="/conclusion/{conclusion_id}/paragraph/{paragraph_id}",
    *    methods={"DELETE"},
    *    name="conclusion.paragraph.delete"
    * )
    *
    * @Entity("conclusion", options={"id" = "conclusion_id"})
    * @Entity("paragraph", options={"id" = "paragraph_id"})
    *
    * @noinspection PhpUnused
    *
    * @param Conclusion $conclusion
    * @param Paragraph $paragraph
    * @param Delete\Handler $handler
    * @param DTOBuilder $builder
    */
   public function deleteParagraph(Conclusion $conclusion, Paragraph $paragraph, Delete\Handler $handler, DTOBuilder $builder)
   {
      $this->denyAccessUnlessGranted(ParagraphAccess::EDIT_PARAGRAPH, $paragraph);
      $this->validateParagraphConclusion($paragraph, $conclusion);
      /** @var Delete\DTO $dto */
      $dto = $builder->buildValidDTOFromArray(Delete\DTO::class, [
         'paragraph_id' => $paragraph->getId()->getValue()
      ]);
      $handler->handler($dto);
   }


   /**
    * @OA\Patch(
    *    path="/conclusion/{conclusion_id}/paragraph/{paragraph_id}/parent",
    *    summary="Указать родителя для параграфа",
    *    tags={"Conclusion Paragraph"},
    *
    *    @OA\Parameter(name="conclusion_id", in="path", required=true,
    *       @OA\Schema(ref="#/components/schemas/ConclusionId")
    *    ),
    *    @OA\Parameter(name="paragraph_id", in="path", required=true,
    *       @OA\Schema(type="integer")
    *    ),
    *
    *    @OA\RequestBody(@OA\JsonContent(ref="#/components/schemas/SetParagraphsParentDTO")),
    *
    *    @OA\Response(response="200", description="ok", @OA\JsonContent(ref="#/components/schemas/ApiResponse")),
    * )
    *
    * @Route(
    *    path="/conclusion/{conclusion_id}/paragraph/{paragraph_id}/parent",
    *    methods={"PATCH"},
    *    name="conclusion.paragraph.setParent"
    * )
    *
    * @Entity("conclusion", options={"id" = "conclusion_id"})
    * @Entity("paragraph", options={"id" = "paragraph_id"})
    *
    * @noinspection PhpUnused
    *
    * @param Conclusion $conclusion
    * @param Paragraph $paragraph
    * @param SetParent\Handler $handler
    * @param ParagraphRepository $paragraphs
    * @param DTOBuilder $builder
    */
   public function setNewOrder(
       Conclusion $conclusion,
       Paragraph $paragraph,
       SetParent\Handler $handler,
       ParagraphRepository $paragraphs,
       DTOBuilder $builder
   )
   {
      $this->denyAccessUnlessGranted(ParagraphAccess::EDIT_PARAGRAPH, $paragraph);
      $this->validateParagraphConclusion($paragraph, $conclusion);
      /** @var SetParent\DTO $dto */
      $dto = $builder->buildValidDTO(
          SetParent\DTO::class,
          ['paragraph_id'],
          ['paragraph_id' => $paragraph->getId()->getValue()]
      );

      if ($dto->parent_id === null) {
         $this->denyAccessUnlessGranted(ConclusionAccess::EDIT_STRUCTURE, $conclusion);
      } else {
         $parent = $paragraphs->get(Id::of($dto->parent_id));
         $this->denyAccessUnlessGranted(ParagraphAccess::EDIT_STRUCTURE, $parent);
      }

      $handler->handle($dto);
   }


   /**
    * @Route(
    *    path="/conclusion/{conclusion_id}/paragraph/{paragraph_id}/certificates",
    *    methods={"PUT"},
    *    name="conclusion.paragraph.set-certificates"
    * )
    *
    * @Entity("paragraph", options={"id" = "paragraph_id"})
    *
    * @noinspection PhpUnused
    *
    * @param Paragraph $paragraph
    * @param DTOBuilder $builder
    * @param SetCertificates\Handler $handler
    * @return Paragraph
    */
   public function setCertificates(Paragraph $paragraph, DTOBuilder $builder, SetCertificates\Handler $handler)
   {
      /** @var SetCertificates\DTO $dto */
      $dto = $builder->buildValidDTO(SetCertificates\DTO::class, ['paragraph_id'], [
         'paragraph_id' => $paragraph->getId()->getValue(),
      ]);

      return $handler->handle($dto);
   }


   /**
    *  Кидает исключение, если параграф не относится к заключению
    *
    * @param Paragraph $paragraph
    * @param Conclusion $conclusion
    */
   private function validateParagraphConclusion(Paragraph $paragraph, Conclusion $conclusion): void
   {
      if (!($c = $paragraph->getConclusion()) instanceof Conclusion || !$c->getId()->isEqual($conclusion->getId())) {
         throw new NotFoundHttpException();
      }
   }
}
