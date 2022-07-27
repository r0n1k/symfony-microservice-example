<?php

namespace App\Services\Authentication\Voter;

use App\Domain\Project\Entity\Conclusion\Conclusion;
use App\Domain\Project\Entity\Users\User\Id;
use App\Domain\Project\Entity\Users\User\User;
use App\Domain\Project\Repository\Users\ProjectUserAssignment\ProjectUserAssignmentRepository;
use App\Domain\Project\Repository\Users\User\UserRepository;
use App\Services\Authentication\UserIdentity;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\Voter\Voter;

class ConclusionAccess extends Voter
{

   public const EDIT_STRUCTURE = 'CONCLUSION_EDIT_STRUCTURE';
   public const VIEW = 'CONCLUSION_VIEW';

   /**
    * @var UserRepository
    */
   private UserRepository $users;
   /**
    * @var ProjectUserAssignmentRepository
    */
   private ProjectUserAssignmentRepository $assignments;

   public function __construct(UserRepository $users, ProjectUserAssignmentRepository $assignments)
   {
      $this->users = $users;
      $this->assignments = $assignments;
   }

   protected function supports($attribute, $subject)
    {
        // replace with your own logic
        // https://symfony.com/doc/current/security/voters.html
        return in_array($attribute, [self::EDIT_STRUCTURE, self::VIEW], true)
            && $subject instanceof Conclusion;
    }

   /**
    * @param string $attribute
    * @param Conclusion $subject
    * @param TokenInterface $token
    * @return bool
    */
   protected function voteOnAttribute($attribute, $subject, TokenInterface $token)
    {
       $userIdentity = $token->getUser();
       if (!$userIdentity instanceof UserIdentity) {
          return false;
       }
       $user = $this->users->get(Id::of($userIdentity->getId()));

       switch ($attribute) {
          case self::VIEW:
             return $this->canView($subject, $user);
          case self::EDIT_STRUCTURE:
             return $this->canEditStructure($subject, $user);
          default:
             return false;
       }
    }

   private function canView(Conclusion $subject, User $user)
   {

      $userRole = $user->getRole();

      if ($userRole->isClient()) {
         return $subject->getIsAccessibleToClient();
      }

      return true;
   }

   private function canEditStructure(Conclusion $subject, User $user)
   {
      $userRole = $user->getRole();
      if ($userRole->isProjectManager() || $userRole->isAdmin()) {
         return true;
      }

      $project = $subject->getProject();
      $userAssignment = $this->assignments->findForProjectAndUser($project, $user);
      if (!$userAssignment) {
         return false;
      }
      if ($userAssignment->getRole()->isMainExpert()) {
         return true;
      }

      return false;
   }
}
