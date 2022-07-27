<?php /** @noinspection PhpIllegalPsrClassPathInspection */
/** @noinspection PhpFieldAssignmentTypeMismatchInspection */
/** @noinspection PhpUnused */

namespace App\Tests\Http\Template;
use App\Domain\Project\Entity\Users\User\Role;
use App\Domain\Template\Entity\Template;
use App\Domain\Template\UseCase\Rename\Handler;
use App\Http\Controller\Template\TemplateController;
use App\Tests\FunctionalTester;
use JsonException;


/**
 * Class RenameCest
 * @package App\Tests\Http\Template
 *
 * @see Handler
 * @see TemplateController::rename()
 */
class RenameCest
{
   /**
    * @var Template
    */
   private Template $template;

   public function _before(FunctionalTester $I)
    {
       $this->template = $I->have(Template::class);
       $I->amLoggedInWithRole(Role::admin());
    }

   /**
    * @param FunctionalTester $I
    * @throws \JsonException
    */
   public function renameTemplate(FunctionalTester $I)
    {
       $I->seeInRepository(Template::class, ['id' => $this->template->getId()]);
       $url = $I->grabRoute('template.rename', ['template_id' => $this->template->getId()]);
       $I->sendPATCH($url, json_encode(['name' => $name = $I->faker()->name], JSON_THROW_ON_ERROR | JSON_THROW_ON_ERROR, 512));
       $I->seeResponseCodeIs(200);
       $I->seeInRepository(Template::class, ['id' => $this->template->getId(), 'title' => $name]);
    }

   /**
    * @param FunctionalTester $I
    * @throws JsonException
    */
    public function renameBasicTemplateCausesError(FunctionalTester $I)
    {
       $templateId = $I->haveTemplate(['Name' => 'basic', 'Paragraphs' => []]);
       $url = $I->grabRoute('template.rename', ['template_id' => $templateId]);
       $I->sendPATCH($url, json_encode(['name' => $I->faker()->name], JSON_THROW_ON_ERROR | JSON_THROW_ON_ERROR, 512));
       $I->seeResponseCodeIs(404);
    }
}
