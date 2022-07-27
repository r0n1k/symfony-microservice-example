<?php


namespace App\Http\Formatter\Formatters;


use App\Domain\Project\Entity\Users\ProjectUserAssignment\ProjectUserAssignment;
use App\Http\Formatter\Base\EntityFormatter;
use App\Http\Formatter\Base\FormatEvent;

class ProjectUserAssignmentFormatter extends EntityFormatter
{

   /**
    * @param ProjectUserAssignment $assignment
    * @return mixed|void
    */
   public function format($assignment)
   {
      $user = $assignment->getUser();

      return [
         'id' => $user->getId()->getValue(),
         'full_name' => $user->getFullName(),
         'email' => (string)$user->getEmail(),
         'role' => (string)$user->getRole(),
         'assignment_role' => (string)$assignment->getRole(),
         'certificates' => $user->getCertificates(),
      ];
   }

   protected function supports(FormatEvent $event): bool
   {
      return $event->getFormattableData() instanceof ProjectUserAssignment;
   }
}
