<?php
/** @noinspection PhpUnused */

namespace App\Tests\Domain\Template;

use App\Domain\Project\Entity\Conclusion\Conclusion;
use App\Domain\Project\Entity\Users\User\User;
use App\Domain\Template\Entity\Id;
use App\Domain\Template\Entity\Template;
use App\Domain\Template\Entity\TemplateParagraph\Id as ParagraphId;
use App\Domain\Template\Entity\TemplateParagraph\TemplateParagraph;
use App\Domain\Template\Entity\TemplateParagraph\Title as ParagraphTitle;
use App\Domain\Template\Entity\Title;
use App\Domain\Template\UseCase\Delete\Handler;
use App\Http\Controller\Template\TemplateController;
use App\Tests\FunctionalTester;

/**
 * Class DeleteCest
 * @package App\Tests\Domain\Template
 *
 * @see Handler
 * @see TemplateController::delete()
 */
class DeleteCest
{
   /**
    * @var User
    */
   private User $user;
   /**
    * @var Conclusion
    */
   private Conclusion $conclusion;
   /**
    * @var string
    */
   private string $templateId;
   /**
    * @var int
    */
   private int $paragraphId;

   public function _before(FunctionalTester $I)
   {
      $this->user = $I->amLoggedIn();


      $this->templateId = (string)$I->haveInRepository(Template::class, [
         'id' => new Id($I->faker()->uuid),
         'title' => new Title($I->faker()->name),
         'isBasic' => false,
      ]);
      $template = $I->grabEntityFromRepository(Template::class, ['id' => $this->templateId]);
      $this->paragraphId = $I->haveInRepository(
         TemplateParagraph::class, [
            'isBasic' => false,
            'template' => $template,
            'title' => new ParagraphTitle($I->faker()->name),
            'id' => new ParagraphId($I->faker()->randomNumber(6)),
         ]
      )->getValue();
      $this->conclusion = $I->have(Conclusion::class, ['template' => $template]);
   }

   // tests
   public function deleteTemplate(FunctionalTester $I)
   {
      $I->canSeeInRepository(Conclusion::class, ['id' => (string)$this->conclusion->getId(), 'templateId' => $this->templateId]);

      $url = $I->grabRoute('template.delete', ['template_id' => $this->templateId]);
      $I->sendDELETE($url);
      $I->seeResponseCodeIs(200);
      $I->dontSeeInRepository(Template::class, ['id' => $this->templateId]);
//      $I->dontSeeInRepository(Conclusion::class, ['id' => (string)$this->conclusion->getId(), 'templateId' => $this->templateId]);
      $I->dontSeeInRepository(TemplateParagraph::class, ['id' => $this->paragraphId]);
   }

   public function deleteBasicTemplate(FunctionalTester $I)
   {
      $id = $I->haveTemplate([
         'Name' => 'test',
         'Paragraphs' => [],
      ]);

      $url = $I->grabRoute('template.delete', ['template_id' => $id]);
      $I->sendDELETE($url);
      $I->seeResponseCodeIsClientError();
   }
}
