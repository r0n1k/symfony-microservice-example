<?php
/** @noinspection PhpIllegalPsrClassPathInspection */
/** @noinspection PhpUnused */

namespace App\Tests\Http\Conclusion\Paragraph;

use App\Domain\Project\Entity\Conclusion\Conclusion;
use App\Domain\Project\Entity\Conclusion\Paragraph\Paragraph;
use App\Domain\Project\Entity\Users\User\Role;
use App\Domain\Project\Event\Conclusion\Paragraph\ParagraphChanged;
use App\Domain\Project\UseCase\Conclusion\Paragraph\Rename\DTO;
use App\Tests\FunctionalTester;
use JsonException;

class RenameCest
{
   /**
    * @var string
    */
   private string $url;

   public function _before(FunctionalTester $I)
   {
      $conclusion = $I->have(Conclusion::class);
      $user = $I->amLoggedInWithRole(Role::admin());
      $project = $conclusion->getProject();
      $I->assignUserToProject($user, $project);

      $paragraph = $I->have(Paragraph::class, ['conclusion' => $conclusion]);
      $this->url = $I->grabRoute('conclusion.paragraph.rename', [
         'conclusion_id' => $conclusion->getId()->getValue(),
         'paragraph_id' => $paragraph->getId()->getValue(),
      ]);
   }


   /**
    * @param FunctionalTester $I
    * @throws JsonException
    */
   public function rename(FunctionalTester $I)
   {
      $dto = new DTO();
      $dto->title = $I->faker()->name;
      $I->sendPATCH($this->url, json_encode($dto, JSON_THROW_ON_ERROR | JSON_THROW_ON_ERROR, 512));
      $I->seeResponseCodeIs(200);
      $I->seeDispatchedDomainEvent(ParagraphChanged::class);
   }
}
