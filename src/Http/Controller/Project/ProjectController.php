<?php


namespace App\Http\Controller\Project;

use App\Domain\Project\Entity\Project\Id;
use App\Domain\Project\Entity\Project\Project;
use App\Domain\Project\Repository\Project\ProjectRepository;
use App\Http\Controller\ApiController;
use App\Http\Formatter\UnformattedResponse;
use App\Http\Services\DTOBuilder\DTOBuilder;
use App\Services\Project\ProjectFetcherInterface;
use OpenApi\Annotations as OA;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Entity;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Exception\ConflictHttpException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Routing\Annotation\Route;
use App\Domain\Project\UseCase\Project\Upsert;

class ProjectController extends ApiController
{

   /**
    * @var ProjectRepository
    */
   private ProjectRepository $projects;


   public function __construct(ProjectRepository $repository)
   {
      $this->projects = $repository;
   }

   /**
    * @OA\Get(
    *    path="/project/{project_id}",
    *    description="Синхронизировать проект",
    *
    *    tags={"Projects"},
    *
    *    @OA\Parameter(name="project_id", in="path", @OA\Schema(type="string", format="uuid"), required=true),
    *
    *    @OA\Response(response="200", description="Проект",
    *       @OA\JsonContent(allOf={
    *          @OA\Schema(ref="#/components/schemas/ApiResponse"),
    *          @OA\Schema(type="object", @OA\Property(property="data", ref="#/components/schemas/Project")),
    *       })
    *    ),
    * )
    *
    * @Route(name="project.sync", path="/project/{project_id}", methods={"GET"})
    *
    * @noinspection PhpUnused
    *
    * @param $project_id
    * @param ProjectFetcherInterface $fetcher
    * @param Upsert\Handler $handler
    * @return Project
    */
   public function sync($project_id, ProjectFetcherInterface $fetcher, Upsert\Handler $handler): Project
   {
      return $handler->handle($fetcher->fetch($project_id));
   }

   /**
    * @OA\Get(
    *    path="/projects",
    *    description="Получить список проектов",
    *
    *    tags={"Projects"},
    *
    *    @OA\Response(response="200", description="Список проектов",
    *       @OA\JsonContent(allOf={
    *          @OA\Schema(ref="#/components/schemas/ApiResponse"),
    *          @OA\Schema(type="object",
    *             @OA\Property(property="data", type="array",
    *                @OA\Items(ref="#/components/schemas/Project"),
    *             ),
    *          ),
    *       })
    *    ),
    * )
    *
    * @Route(name="project.all", path="/projects", methods={"GET"})
    *
    * @noinspection PhpUnused
    *
    * @param ProjectRepository $repository
    * @return Project[]
    */
   public function getAll(ProjectRepository $repository): array
   {
      return $repository->findAll();
   }


   /**
    * @OA\Post(
    *    path="/project",
    *    description="Создание проекта",
    *    operationId="project:create",
    *
    *    @OA\RequestBody(@OA\JsonContent(ref="#/components/schemas/UpsertProjectDTO")),
    *
    *    tags={"Projects"},
    *
    *    @OA\Response(response="409", description="Проект уже существует",
    *       @OA\JsonContent(allOf={
    *          @OA\Schema(ref="#/components/schemas/ApiResponse"),
    *       })
    *    ),
    *    @OA\Response(response="201", description="Проект создан",
    *       @OA\JsonContent(allOf={
    *          @OA\Schema(ref="#/components/schemas/ApiResponse"),
    *          @OA\Schema(type="object", @OA\Property(property="data", ref="#/components/schemas/Project")),
    *       })
    *    ),
    * )
    * @OA\Put(
    *    path="/project/{project_id}",
    *    description="Обновление проекта",
    *    operationId="project:update",
    *
    *    @OA\Parameter(name="project_id", in="path", @OA\Schema(ref="#/components/schemas/ProjectId"), required=true),
    *
    *    @OA\RequestBody(@OA\JsonContent(ref="#/components/schemas/UpsertProjectDTO")),
    *
    *    tags={"Projects"},
    *
    *    @OA\Response(response="404", description="Проект не найден",
    *       @OA\JsonContent(allOf={
    *          @OA\Schema(ref="#/components/schemas/ApiResponse"),
    *       })
    *    ),
    *    @OA\Response(response="200", description="Проект обновлён",
    *       @OA\JsonContent(allOf={
    *          @OA\Schema(ref="#/components/schemas/ApiResponse"),
    *          @OA\Schema(type="object", @OA\Property(property="data", ref="#/components/schemas/Project")),
    *       })
    *    ),
    * )
    * @Route(name="project.create", path="/project", methods={"POST"}, format="json")
    * @Route(name="project.update", path="/project/{project_id}", methods={"PUT"}, format="json")
    *
    * @Entity("project", options={"id" = "project_id"}, isOptional=true)
    *
    * @param Request $request
    * @param Upsert\Handler $handler
    * @param Project|null $project
    * @param DTOBuilder $builder
    * @return UnformattedResponse|JsonResponse
    */
   public function upsert(Request $request, Upsert\Handler $handler, ?Project $project, DTOBuilder $builder)
   {
      if (!$project instanceof Project && $request->isMethod('PUT')) {
         throw new NotFoundHttpException('Project not found');
      }

      $ignored_attributes = [];
      $attributes = [];
      if ($request->isMethod('PUT')) {
         $ignored_attributes[] = 'project_id';
         $attributes['project_id'] = (string)$project->getId();
      }
      /** @var Upsert\DTO $dto */
      $dto = $builder->buildValidDTO(Upsert\DTO::class, $ignored_attributes, $attributes);
      $dto->dictionaries = $builder->buildValidDTOFromArray(Upsert\DictionaryDTO::class, (array)$dto->dictionaries);

      if ($request->isMethod('POST') && $this->projects->exists(new Id($dto->project_id))) {
         throw new ConflictHttpException('Project already exists. Use put method instead.');
      }

      $responseCode = $request->isMethod('POST') ? 201 : 200;
      $project = $handler->handle($dto);

      return new UnformattedResponse($project, $responseCode);
   }
}
