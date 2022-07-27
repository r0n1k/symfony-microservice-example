<?php
/** @noinspection PhpIllegalPsrClassPathInspection */
/** @noinspection PhpFieldAssignmentTypeMismatchInspection */
/** @noinspection PhpUnused */

namespace App\Tests\Http\Conclusion\Paragraph;

use App\Domain\Project\Entity\Conclusion\Conclusion;
use App\Domain\Project\Entity\Conclusion\Paragraph\Paragraph;
use App\Domain\Project\Entity\Users\User\Role;
use App\Domain\Project\Event\Conclusion\Paragraph\ParagraphChanged;
use App\Tests\FunctionalTester;

class SetParentCest
{
   private Conclusion $conclusion;
   private Paragraph $paragraph;
   private Paragraph $sibling;

   public function _before(FunctionalTester $I)
   {
      $user = $I->amLoggedInWithRole(Role::admin());
      $this->conclusion = $conclusion = $I->make(Conclusion::class);
      $this->paragraph = $I->have(Paragraph::class, ['conclusion' => $this->conclusion]);
      $this->sibling = $I->have(Paragraph::class, ['conclusion' => $this->conclusion, 'parent' => $this->paragraph]);

      $project = $conclusion->getProject();

      $I->assignUserToProject($user, $project);
   }

   /**
    * @param FunctionalTester $I
    */
   public function removeParent(FunctionalTester $I)
   {
      $url = $I->grabRoute('conclusion.paragraph.setParent', [
         'conclusion_id' => $this->conclusion->getId()->getValue(),
         'paragraph_id' => $this->sibling->getId()->getValue(),
      ]);
      $I->sendPATCH($url, ['parent_id' => null]);
      $I->seeResponseCodeIs(200);
      $I->seeDispatchedDomainEvent(ParagraphChanged::class);

   }

   public function recursiveParent(FunctionalTester $I)
   {
      $url = $I->grabRoute('conclusion.paragraph.setParent', [
         'conclusion_id' => $this->conclusion->getId()->getValue(),
         'paragraph_id' => $this->paragraph->getId()->getValue(),
      ]);
      $I->sendPATCH($url, ['parent_id' => $this->sibling->getId()->getValue()]);
      $I->seeResponseCodeIs(400);
      $I->dontSeeDispatchedDomainEvent(ParagraphChanged::class);
   }

}
