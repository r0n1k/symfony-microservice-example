<?php
/**
 * @noinspection PhpUnused
 * @noinspection PhpDeprecationInspection
 */
namespace App\Tests\Template;

use App\Domain\Template\Entity\Template;
use App\Domain\Template\TemplateFileRepository;
use App\Services\YamlParser;
use App\Tests\UnitTester;
use LogicException;

class TemplateFileRepositoryCest
{
   /**
    * @var TemplateFileRepository
    */
   private $repo;

   public function _before(UnitTester $I)
   {
      $this->repo = new class(new YamlParser()) extends TemplateFileRepository {
         protected function basicTemplatesDirectory(): string
         {
            return $this->_test_dir;
         }

         public $_test_dir;
      };
      $this->repo->_test_dir = $I->grabTemplatesDir();
   }

   // tests
   public function testBuildFromFile(UnitTester $I)
   {
      $uuid = $I->haveTemplate([
         'Name' => 'Test template',
         'Paragraphs' => [
            [
               'Name' => 'P1',
               'Children' => [
                  [
                     'Name' => 'P1.1',
                     'BlockKind' => 'dict',
                  ],
                  [
                     'Name' => 'P1.2',
                     'BlockKind' => 'text',
                  ]
               ],
            ]
         ]
      ]);

      $template = $this->repo->find($uuid);
      $I->assertInstanceOf(Template::class, $template);
      $I->assertCount(1, $template->getParagraphs());
      $I->assertCount(2, $template->getParagraphs()[0]->getChildren());
   }

   public function testWrongTemplate(UnitTester $I)
   {

      $uuid = $I->haveTemplate([
         'Name' => 'Test template',
         'Paragraphs' => [
            [
               'Name' => 'P1',
               'BlockKind' => 'wrong',
            ]
         ]
      ]);

      $I->expectException(LogicException::class, function() use ($uuid) {
         $this->repo->find($uuid);
      });
   }
}
