<?php

namespace App\Services\Authentication\Voter;

use App\Domain\Project\Entity\Conclusion\Paragraph\Paragraph;
use App\Domain\Project\Entity\Users\User\Certificate\Certificate;
use App\Domain\Project\Entity\Users\User\Id;
use App\Domain\Project\Repository\Users\ProjectUserAssignment\ProjectUserAssignmentRepository;
use App\Domain\Project\Repository\Users\User\UserRepository;
use App\Services\Authentication\UserIdentity;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\Voter\Voter;

class ParagraphAccess extends Voter
{

   public const EDIT_STRUCTURE = 'PARAGRAPH_EDIT_STRUCTURE';
   public const EDIT_BLOCK = 'PARAGRAPH_BLOCK_EDIT';
   public const RENAME = 'PARAGRAPH_RENAME';
   public const EDIT_PARAGRAPH = 'PARAGRAPH_EDIT';

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
      return in_array($attribute, [self::EDIT_STRUCTURE, self::RENAME, self::EDIT_PARAGRAPH, self::EDIT_BLOCK], true)
         && $subject instanceof Paragraph;
   }

   /**
    * @param string $attribute
    * @param Paragraph $subject
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
      $userRole = $user->getRole();

      if ($userRole->isProjectManager() || $userRole->isAdmin()) {
         return true;
      }

      $project = $subject->getConclusion()->getProject();
      $userAssignment = $this->assignments->findForProjectAndUser($project, $user);
      if (!$userAssignment) {
         return false;
      }
      if ($userAssignment->getRole()->isMainExpert()) {
         return true;
      }

      $userHasCertificate = $this->userHasCertificate($user->getCertificates(), $subject);

      switch ($attribute) {
         case self::RENAME:
         case self::EDIT_BLOCK:
         case self::EDIT_STRUCTURE:
         case self::EDIT_PARAGRAPH:
            return $userHasCertificate;
      }

      return false;
   }

   /**
    * @param Certificate[] $userCertificates
    * @param Paragraph $paragraph
    * @return bool
    */
   private function userHasCertificate($userCertificates, Paragraph $paragraph): bool
   {
      foreach ($userCertificates as $userCert) {
         foreach ($paragraph->getCertificates() as $paragraphCert) {
            if ($userCert->getScope() === $paragraphCert->getScope()) {
               return true;
            }
         }
      }

      if (($parent = $paragraph->getParent()) instanceof Paragraph) {
         return $this->userHasCertificate($userCertificates, $parent);
      }

      return false;
   }
}
