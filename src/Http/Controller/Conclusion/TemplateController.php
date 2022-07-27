<?php


namespace App\Http\Controller\Conclusion;


use App\Domain\Project\Entity\Conclusion\Conclusion;
use App\Domain\Project\UseCase\Conclusion\SetTemplate\DTO;
use App\Domain\Project\UseCase\Conclusion\SetTemplate\Handler;
use App\Http\Controller\ApiController;
use App\Http\Services\DTOBuilder\DTOBuilder;
use Symfony\Component\Routing\Annotation\Route;
use OpenApi\Annotations as OA;

/** @noinspection PhpUnused */
class TemplateController extends ApiController
{

   /**
    * @OA\Patch(
    *    path="/conclusion/{conclusion_id}/template",
    *    tags={"Conclusion"},
    *    summary="Указать шаблон для существующего заключения",
    *    description="Если заключение пустое, без созданных разделов, то создастся дерево по шаблону,
    *     иначе ошибка
    *    ",
    *
    *    @OA\RequestBody(@OA\JsonContent(ref="#/components/schemas/ConclusionSetTemplateDTO")),
    *
    *    @OA\Parameter(name="conclusion_id", in="path", description="ID заключения", required=true,
    *       @OA\Schema(ref="#/components/schemas/ConclusionId"),
    *    ),
    *
    *    @OA\Response(
    *       response="200", description="Ok", @OA\JsonContent(allOf={
    *          @OA\Schema(ref="#/components/schemas/ApiResponse"),
    *          @OA\Schema(type="object", @OA\Property(property="data", ref="#/components/schemas/Conclusion")),
    *       }),
    *    )
    * )
    *
    * @Route(path="/conclusion/{id}/template", methods={"PATCH"}, name="conclusion.template.update")
    *
    * @noinspection PhpUnused
    *
    * @param Conclusion $conclusion
    * @param Handler $handler
    * @param DTOBuilder $builder
    * @return mixed
    */
   public function setTemplateForConclusion(Conclusion $conclusion, Handler $handler, DTOBuilder $builder)
   {
      /** @var DTO $dto */
      $dto = $builder->buildValidDTO(DTO::class, ['conclusion_id'], ['conclusion_id' => $conclusion->getId()]);
      return $handler->handle($dto);
   }

}
