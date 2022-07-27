<?php


namespace App\Http\Formatter\Formatters\Project;

use App\Domain\Project\Entity\Project\Project;
use App\Domain\Project\Entity\Users\ProjectUserAssignment\ProjectUserAssignment;
use App\Http\Formatter\Base\FormatEvent;
use App\Http\Formatter\Base\EntityFormatter;
use OpenApi\Annotations as OA;

/**
 * @noinspection PhpUnused
 */
class ProjectFormatter extends EntityFormatter
{
    /**
     * @OA\Schema(schema="Project", type="object",
     *    @OA\Property(property="id", ref="#/components/schemas/ProjectId"),
     *    @OA\Property(property="name", ref="#/components/schemas/ProjectName"),
     *    @OA\Property(property="state", ref="#/components/schemas/ProjectState"),
     *    @OA\Property(property="userAssignments", type="array",
     *       @OA\Items(type="object",
     *          @OA\Property(property="user", ref="#/components/schemas/User"),
     *          @OA\Property(property="role", ref="#/components/schemas/UserAssignmentRole"),
     *       )
     *    )
     * )
     * @param Project $entity
     * @return array|mixed
     */
   public function format($entity)
   {
      return [
         'id' => (string)$entity->getId(),
         'name' => (string)$entity->getName(),
         'state' => (string)$entity->getState(),
         'userAssignments' => $entity->getUsers()
            ->map(static function (ProjectUserAssignment $assignment) {
               return [
                  'user' => $assignment->getUser(),
                  'role' => $assignment->getRole(),
               ];
            }),
      ];
   }

   protected function supports(FormatEvent $event): bool
   {
      return $event->getFormattableData() instanceof Project;
   }
}
