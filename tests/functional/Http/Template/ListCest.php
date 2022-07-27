<?php /** @noinspection PhpIllegalPsrClassPathInspection */

/** @noinspection PhpUnused */

namespace App\Tests\Http\Template;
use App\Domain\Project\Entity\Users\User\Role;
use App\Tests\FunctionalTester;
use JsonException;

class ListCest
{

   public function _before(FunctionalTester $I)
   {
      $I->amLoggedInWithRole(Role::admin());
   }


   /**
    * @param FunctionalTester $I
    * @throws JsonException
    */
   public function fetchAll(FunctionalTester $I)
   {
      $I->amGoingTo('Fetch all templates and see both basic templates and users templates');
      $rawTemplateOneId = $I->haveTemplate($rawTemplateOne = [
         'Name' => 'Template 1',
         'Paragraphs' => [
            [
               'Name' => 'One',
               'BlockKind' => 'text',
            ],
         ],
      ]);
      $rawTemplateTwoId = $I->haveTemplate($rawTemplateTwo = [
         'Name' => 'Template 2',
         'Paragraphs' => [
            [
               'Name' => 'Two',
               'BlockKind' => 'dict',
            ],
         ]
      ]);
      $I->sendGET($I->grabRoute('template.list'));
      $I->seeResponseCodeIs(200);
      $I->seeResponseMatchesJsonType([
         [
            'id' => 'string',
            'name' => 'string',
         ],
         [
            'id' => 'string',
            'name' => 'string',
         ],
      ], '$.data');
      $response = json_decode($I->grabResponse(), true, 512, JSON_THROW_ON_ERROR | JSON_THROW_ON_ERROR);
      $templates = $response['data'];
      $I->assertCount(2, $templates);

      $templateOne = array_values(array_filter($templates, static function($template) use ($rawTemplateOneId) {
         return strtoupper($template['id']) === strtoupper($rawTemplateOneId);
      }))[0];

      $templateTwo = array_values(array_filter($templates, static function($template) use ($rawTemplateTwoId) {
         return strtoupper($template['id']) === strtoupper($rawTemplateTwoId);
      }))[0];


      $I->assertCount(2, $templates);
      $I->assertEquals($rawTemplateOneId, $templateOne['id']);
      $I->assertEquals($rawTemplateOne['Name'], $templateOne['name']);
      $I->assertEquals($rawTemplateTwoId, $templateTwo['id']);
      $I->assertEquals($rawTemplateTwo['Name'], $templateTwo['name']);
   }
}
