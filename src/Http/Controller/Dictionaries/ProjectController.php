<?php


namespace App\Http\Controller\Dictionaries;


use App\Domain\Project\Entity\Dictionary\Dictionary;
use App\Domain\Project\Entity\Project\Project;
use App\Domain\Project\UseCase\Dictionary\UpsertProject;
use App\Domain\Project\UseCase\Dictionary\Delete;
use App\Http\Services\DTOBuilder\DTOBuilder;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Entity;
use Symfony\Component\Routing\Annotation\Route;
use OpenApi\Annotations as OA;

class ProjectController
{

   /**
    * @OA\Post(
    *    path="/dictionary/project/{project_id}/{dictionary_key}",
    *    tags={"Dictionaries"},
    *    summary="Добавить словарное значение проекта",
    *    @OA\Parameter(name="project_id", in="path", description="ID проекта", @OA\Schema(type="string", format="uuid"),
    *       required=true),
    *    @OA\Parameter(name="dictionary_key", in="path", description="Ключ словаря", @OA\Schema(type="string"),
    *       required=true),
    *
    *    @OA\RequestBody(@OA\JsonContent(ref="#/components/schemas/UpsertDictionaryDTO")),
    *
    *    @OA\Response(response="201", description="ok", @OA\JsonContent(allOf={
    *       @OA\Schema(ref="#/components/schemas/ApiResponse"),
    *       @OA\Schema(ref="#/components/schemas/Dictionary"),
    *    }))
    * )
    * @OA\Put(
    *    path="/dictionary/project/{project_id}/{dictionary_key}",
    *    tags={"Dictionaries"},
    *    summary="Обновить словарное значение проекта",
    *    @OA\Parameter(name="project_id", in="path", description="ID проекта", @OA\Schema(type="string", format="uuid"),
    *       required=true),
    *    @OA\Parameter(name="dictionary_key", in="path", description="Ключ словаря", @OA\Schema(type="string"),
    *       required=true),
    *
    *    @OA\RequestBody(@OA\JsonContent(ref="#/components/schemas/UpsertDictionaryDTO")),
    *
    *    @OA\Response(response="200", description="ok", @OA\JsonContent(allOf={
    *       @OA\Schema(ref="#/components/schemas/ApiResponse"),
    *       @OA\Schema(ref="#/components/schemas/Dictionary"),
    *    }))
    * )
    * @Route(path="/dictionary/project/{project_id}/{dictionary_key}", methods={"POST", "PUT"}, name="dictionary.project.upsert")
    * @Entity("project", options={"id" = "project_id"})
    *
    * @noinspection PhpUnused
    *
    * @param DTOBuilder $builder
    * @param UpsertProject\Handler $handler
    * @param Project $project
    * @param string $dictionary_key
    * @return Dictionary
    */
   public function addProjectDictionary(DTOBuilder $builder,
                                        UpsertProject\Handler $handler,
                                        Project $project,
                                        string $dictionary_key)
   {

      $projectId = $project->getId()->getValue();

      /** @var UpsertProject\DTO $dto */
      $dto = $builder->buildValidDTO(
         UpsertProject\DTO::class,
         ['project_id', 'dictionary_key'],
         ['project_id' => $projectId, 'dictionary_key' => $dictionary_key]
      );
      return $handler->handle($dto);
   }


   /**
    * @OA\Delete(
    *    path="/dictionary/project/{project_id}/{dictionary_key}",
    *    tags={"Dictionaries"},
    *    summary="Удалить словарное значение проекта",
    *    @OA\Parameter(name="project_id", in="path", description="ID проекта", @OA\Schema(type="string", format="uuid"),
    *       required=true),
    *    @OA\Parameter(name="dictionary_key", in="path", description="Ключ словаря", @OA\Schema(type="string"),
    *       required=true),
    *
    *    @OA\Response(response="200", description="ok", @OA\JsonContent(allOf={
    *       @OA\Schema(ref="#/components/schemas/ApiResponse"),
    *    }))
    * )
    *
    * @Entity("project", options={"id" = "project_id"})
    * @Route(path="/dictionary/project/{project_id}/{dictionary_key}", methods={"DELETE"}, name="dictionary.project.delete")
    *
    * @noinspection PhpUnused
    *
    * @param DTOBuilder $builder
    * @param Delete\Handler $handler
    * @param Project $project
    * @param string $dictionary_key
    */
   public function removeProjectDictionary(DTOBuilder $builder,
                                           Delete\Handler $handler,
                                           Project $project,
                                           string $dictionary_key)
   {
      /** @var Delete\DTO $dto */
      $dto = $builder->buildValidDTOFromArray(Delete\DTO::class, [
         'project_id' => $project->getId()->getValue(),
         'dictionary_key' => $dictionary_key,
      ]);

      $handler->handle($dto);
   }
}
