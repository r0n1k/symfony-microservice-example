<?php


namespace App\Http\Controller\Project;


use App\Domain\Project\Entity\Users\User\User;
use App\Domain\Project\Repository\Conclusion\ConclusionRepository;
use App\Domain\Project\UseCase\Project\Upsert\Handler;
use App\Http\Controller\ApiController;
use App\Http\Services\AuthorizedUserFactory;
use App\Services\Project\ProjectFetcherInterface;
use OpenApi\Annotations as OA;
use Symfony\Component\Routing\Annotation\Route;

/** @noinspection PhpUnused */

class ConclusionsController extends ApiController
{

   /**
    * @var ConclusionRepository
    */
   private ConclusionRepository $conclusions;

   public function __construct(ConclusionRepository $conclusions)
   {
      $this->conclusions = $conclusions;
   }

   /**
    * @OA\Get(
    *    path="/project/{project_id}/conclusions",
    *    summary="Получить список всех заключений объекта",
    *    tags={"Conclusion"},
    *
    *    @OA\Parameter(name="project_id", in="path", @OA\Schema(type="string", format="uuid"), required=true),
    *
    *    @OA\Response(response="200", description="ok",@OA\JsonContent(allOf={
    *       @OA\Schema(ref="#/components/schemas/ApiResponse"),
    *       @OA\Schema(type="object", @OA\Property(property="data", type="array",
    *          @OA\Items(ref="#/components/schemas/Conclusion"),
    *       ))
    *    }))
    * )
    * @param $project_id
    * @param ProjectFetcherInterface $fetcher
    * @param AuthorizedUserFactory $userFactory
    * @param Handler $handler
    * @return mixed
    *
    * @noinspection PhpUnused
    * @Route(path="/project/{project_id}/conclusions", methods={"GET"}, name="project.list_conclusions")
    */
   public function listConclusionsForProject($project_id,
                                             ProjectFetcherInterface $fetcher,
                                             AuthorizedUserFactory $userFactory,
                                             Handler $handler)
   {
      $project = $handler->handle($fetcher->fetch($project_id));
      $user = $userFactory->getUser();

      if (!$user instanceof User) {
         return [];
      }

      if ($user->getRole()->isClient()) {
         return $this->conclusions->findForProjectAccessibleToClient($project);
      }
      return $this->conclusions->findAllForProject($project);
   }

}
