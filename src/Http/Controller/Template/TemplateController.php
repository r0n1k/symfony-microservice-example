<?php

namespace App\Http\Controller\Template;


use App\Domain\Template\Entity\Template;
use App\Domain\Template\Repository\TemplateRepository;
use App\Domain\Template\UseCase\CreateFromConclusion;
use App\Domain\Template\UseCase\Delete;
use App\Domain\Template\UseCase\Rename;
use App\Http\Controller\ApiController;
use App\Http\Formatter\UnformattedResponse;
use App\Http\Services\DTOBuilder\DTOBuilder;
use OpenApi\Annotations as OA;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Entity;
use Symfony\Component\Routing\Annotation\Route;

/**
 * Class CreateController
 * @package App\Http\Controller\Template
 * @noinspection PhpUnused
 */
class TemplateController extends ApiController
{

   /**
    * @OA\Get(
    *    path="/templates",
    *    summary="Получить доступные шаблоны",
    *    tags={"Conclusion Template"},
    *
    *    @OA\Response(response="200", description="ok", @OA\JsonContent(allOf={
    *       @OA\Schema(ref="#/components/schemas/ApiResponse"),
    *       @OA\Schema(type="object",
    *          @OA\Property(property="data", type="array",
    *             @OA\Items(ref="#/components/schemas/ConclusionTemplate"),
    *          ),
    *       ),
    *    })),
    * )
    * @Route(path="/templates", methods={"GET"}, name="template.list")
    *
    * @noinspection PhpUnused
    * @param TemplateRepository $templates
    * @return Template[]|array
    */
   public function listTemplates(TemplateRepository $templates)
   {
      return $templates->findAll();
   }

   /**
    * @OA\Post(
    *    path="/template",
    *    summary="Создать шаблон из существующего заключения",
    *    tags={"Conclusion Template"},
    *
    *    @OA\RequestBody(@OA\JsonContent(ref="#/components/schemas/CreateTemplateFromConclusionDTO")),
    *
    *    @OA\Response(response="201", description="ok", @OA\JsonContent(allOf={
    *       @OA\Schema(ref="#/components/schemas/ApiResponse"),
    *       @OA\Schema(type="object", @OA\Property(property="data", ref="#/components/schemas/ConclusionTemplate")),
    *    })),
    * )
    * @Route(path="/template", methods={"POST"}, name="template.create_from_conclusion")
    *
    * @noinspection PhpUnused
    * @param DTOBuilder $builder
    * @param CreateFromConclusion\Handler $handler
    * @return mixed
    */
   public function createFromConclusion(DTOBuilder $builder, CreateFromConclusion\Handler $handler)
   {
      /** @var CreateFromConclusion\DTO $dto */
      $dto = $builder->buildValidDTO(CreateFromConclusion\DTO::class);
      return new UnformattedResponse($handler->handle($dto), 201);
   }

   /**
    * @OA\Delete(
    *    path="/template/{template_id}",
    *    summary="Удалить шаблон",
    *    tags={"Template"},
    *
    *    @OA\Parameter(name="template_id", in="path", @OA\Schema(type="string", format="uuid"), required=true),
    *
    *    @OA\Response(response="200", description="ok", @OA\JsonContent(ref="#/components/schemas/ApiResponse")),
    * )
    *
    * @Route(
    *    path="/template/{template_id}",
    *    name="template.delete",
    *    methods={"DELETE"}
    * )
    * @Entity("template", options={"mapping": {"template_id": "id"}})
    *
    * @param Template $template
    * @param Delete\Handler $handler
    */
   public function delete(Template $template, Delete\Handler $handler)
   {
      $dto = new Delete\DTO();
      $dto->template_id = $template->getId();
      $handler->handle($dto);
   }


   /**
    * @OA\Patch(
    *    path="/template/{template_id}/name",
    *    summary="Переименовать шаблон",
    *    tags={"Template"},
    *
    *    @OA\Parameter(name="template_id", in="path", @OA\Schema(type="string", format="uuid"), required=true),
    *
    *    @OA\Response(response="200", description="ok", @OA\JsonContent(allOf={
    *       @OA\Schema(ref="#/components/schemas/ApiResponse"),
    *       @OA\Schema(type="object", @OA\Property(property="data", ref="#/components/schemas/ConclusionTemplate")),
    *    })),
    * )
    *
    * @Route(
    *    path="/template/{template_id}/name",
    *    name="template.rename",
    *    methods={"PATCH"}
    * )
    * @Entity("template", options={"mapping": {"template_id": "id"}})
    *
    * @param Template $template
    * @param Rename\Handler $handler
    * @param DTOBuilder $builder
    * @return mixed
    */
   public function rename(Template $template, Rename\Handler $handler, DTOBuilder $builder)
   {
      /** @var Rename\DTO $dto */
      $dto = $builder->buildValidDTO(Rename\DTO::class, ['template_id'], ['template_id' => $template->getId()]);
      return $handler->handle($dto);
   }

}
