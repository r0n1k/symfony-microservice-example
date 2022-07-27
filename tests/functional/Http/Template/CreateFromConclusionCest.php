<?php /** @noinspection PhpUnhandledExceptionInspection */
/** @noinspection JsonEncodingApiUsageInspection */
/** @noinspection PhpIllegalPsrClassPathInspection */
/** @noinspection PhpFieldAssignmentTypeMismatchInspection */
/** @noinspection PhpUnused */

namespace App\Tests\Http\Template;

use App\Domain\Project\Entity\Conclusion;
use App\Domain\Project\Entity\Dictionary\Dictionary;
use App\Domain\Project\Entity\Users\User\Certificate\Certificate;
use App\Domain\Project\Entity\Users\User\Role;
use App\Domain\Template\Entity\Template;
use App\Domain\Template\Entity\TemplateParagraph\TemplateParagraph;
use App\Domain\Template\UseCase\CreateFromConclusion\DTO;
use App\Tests\FunctionalTester;
use JsonException;
use Ramsey\Uuid\Uuid;

class CreateFromConclusionCest
{
   private string $url;
   private Conclusion\Conclusion $conclusion;
   private Conclusion\Paragraph\Paragraph $conclusionParagraph1_1;
   private Conclusion\Paragraph\Paragraph $conclusionParagraph1_2;
   private Conclusion\Paragraph\Paragraph $conclusionParagraph1;

   public function _before(FunctionalTester $I)
   {
      $I->amLoggedInWithRole(Role::admin());
      $this->url = $I->grabRoute('template.create_from_conclusion');

      $this->conclusion = $I->have(Conclusion\Conclusion::class);
      $this->conclusion->setTemplateId(new Conclusion\TemplateId(Uuid::uuid4()->toString()));
      $I->haveInRepository($this->conclusion);

      $this->conclusionParagraph1 = $I->have(Conclusion\Paragraph\Paragraph::class, ['conclusion' => $this->conclusion]);
      $this->conclusionParagraph1_2 = $I->have(Conclusion\Paragraph\Paragraph::class, [
         'conclusion' => $this->conclusion,
         'parent' => $this->conclusionParagraph1
      ]);
      $this->conclusionParagraph1_1 = $I->have(Conclusion\Paragraph\Paragraph::class, [
         'conclusion' => $this->conclusion,
         'parent' => $this->conclusionParagraph1
      ]);

      /** @var Conclusion\Paragraph\Block\Block $blockOne */
      $blockOne = $I->make(Conclusion\Paragraph\Block\Block::class, ['kind' => Conclusion\Paragraph\Block\Kind::text()]);
      $this->conclusionParagraph1_1->addBlock($blockOne->getId(), $blockOne->getKind(), $blockOne->getState(), $blockOne->getOrder());

      $certificate1 = $I->have(Certificate::class);
      $certificate2 = $I->have(Certificate::class);
      $this->conclusionParagraph1_1->addCertificate($certificate1);
      $this->conclusionParagraph1_1->addCertificate($certificate2);

      /** @var Conclusion\Paragraph\Block\Block $blockTwo */
      $blockTwo = $I->make(Conclusion\Paragraph\Block\Block::class, ['kind' => Conclusion\Paragraph\Block\Kind::dict()]);
      $this->conclusionParagraph1_2->addBlock($blockTwo ->getId(), $blockTwo ->getKind(), $blockTwo ->getState(), $blockTwo->getOrder());
      $I->haveInRepository($this->conclusionParagraph1_1);
      $I->haveInRepository($this->conclusionParagraph1_2);

      // create 2 dictionary values
      $I->have(Dictionary::class, [
         'project' => $this->conclusion->getProject(),
         'block' => $this->conclusionParagraph1_2->getBlocks()->first(),
      ]);
      $I->have(Dictionary::class, [
         'project' => $this->conclusion->getProject(),
         'block' => $this->conclusionParagraph1_2->getBlocks()->first(),
      ]);
   }

   // tests

   /**
    * @param FunctionalTester $I
    * @throws JsonException
    */
   public function createFromConclusion(FunctionalTester $I)
   {
      $data = new DTO();
      $data->conclusion_id = $this->conclusion->getId()->getValue();
      $data->name = $I->faker()->name;

      $I->sendPOST($this->url, json_encode($data, JSON_THROW_ON_ERROR, 512));

      $I->amGoingTo('Ответ корректен');
      $I->seeResponseCodeIs(201);
      $I->seeResponseMatchesJsonType([
         'id' => 'string',
         'name' => 'string:=' . $data->name,
         'is_basic' => 'boolean'
      ], '$.data');

      $response = json_decode($I->grabResponse(), true, 512, JSON_THROW_ON_ERROR);
      $templateId = $response['data']['id'];


      $I->amGoingTo('Шаблон создан');
      /** @var Template $template */
      $template = $I->grabEntityFromRepository(Template::class, ['id' => $templateId]);

      $I->amGoingTo('У заключение поменялся ID шаблона на ID только что созданного');
      $conclusion = $I->grabEntityFromRepository(Conclusion\Conclusion::class, ['id' => $this->conclusion->getId()]);
      $I->assertEquals($templateId, $conclusion->getTemplateId());

      $I->amGoingTo('Параграфы для шаблона созданы');
      /** @var TemplateParagraph[] $paragraphs */
      $paragraphs = $I->grabEntitiesFromRepository(TemplateParagraph::class);
      $I->assertCount(3, $paragraphs);

      $I->amGoingTo('Созданная структура верна');
      $conclusionParagraph1 = $this->conclusionParagraph1;
      $conclusionParagraph1_1 = $this->conclusionParagraph1_1;
      $conclusionParagraph1_2 = $this->conclusionParagraph1_2;

      /** @var TemplateParagraph $templateParagraph1 */
      $templateParagraph1 = $template->getParagraphs()->filter(static function (TemplateParagraph $p) use ($conclusionParagraph1) {
         return $p->getTitle()->getValue() === $conclusionParagraph1->getTitle()->getValue();
      })->first();
      $I->assertInstanceOf(TemplateParagraph::class, $templateParagraph1);
      $I->seeInRepository(TemplateParagraph::class, ['parent' => $templateParagraph1]);
      $I->assertCount(2, $templateParagraph1->getChildren());
      $templateParagraph1_1 = $templateParagraph1->getChildren()
         ->filter(static function (TemplateParagraph $p) use ($conclusionParagraph1_1) {
            return $p->getTitle()->getValue() === $conclusionParagraph1_1->getTitle()->getValue();
         })
         ->first();
      $templateParagraph1_2 = $templateParagraph1->getChildren()
         ->filter(static function (TemplateParagraph $p) use ($conclusionParagraph1_2) {
            return $p->getTitle()->getValue() === $conclusionParagraph1_2->getTitle()->getValue();
         })
         ->first();

      $I->assertInstanceOf(TemplateParagraph::class, $templateParagraph1_1);
      $I->assertInstanceOf(TemplateParagraph::class, $templateParagraph1_2);

      $I->assertEquals($conclusionParagraph1_1->getBlocks()->first()->getKind()->getValue(), $templateParagraph1_1->getBlockKind());
      $I->assertEquals($conclusionParagraph1_2->getBlocks()->first()->getKind()->getValue(), $templateParagraph1_2->getBlockKind());


      $I->amGoingTo('У параграфов сохранились сертификаты');
      $I->assertCount(2, $templateParagraph1_1->getCertificates());

      $I->amGoingTo('У параграфов сохранились словари');
      $I->assertCount(2, $templateParagraph1_2->getDictionaries());
   }
}
