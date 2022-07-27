<?php /** @noinspection SpellCheckingInspection */
/** @noinspection PhpMissingFieldTypeInspection */
/** @noinspection JsonEncodingApiUsageInspection */
/** @noinspection PhpIllegalPsrClassPathInspection */

/** @noinspection PhpUnused */

namespace App\Tests\Http\Conclusion;

use App\Domain\Project\Entity\Conclusion\Conclusion;
use App\Domain\Project\Entity\Conclusion\Paragraph\Block\Block;
use App\Domain\Project\Entity\Conclusion\Paragraph\Block\Kind;
use App\Domain\Project\Entity\Conclusion\Paragraph\Paragraph;
use App\Domain\Project\Entity\Project\Project;
use App\Domain\Project\Entity\Users\User\Role;
use App\Domain\Project\Entity\Users\User\User;
use App\Domain\Project\Event\Conclusion\ConclusionCreated;
use App\Domain\Project\Repository\Dictionary\DictionaryRepository;
use App\Domain\Project\UseCase\Conclusion\Create\DTO;
use App\Tests\FunctionalTester;
use JsonException;

class CreateCest
{
   /**
    * @var string
    */
   private $url;
   /**
    * @var User
    */
   private $user;

   public function _before(FunctionalTester $I)
   {
      $project = $I->have(Project::class);
      $this->url = $I->grabRoute('conclusion.create', ['project_id' => $project->getId()]);
      $this->user = $I->amLoggedInWithRole(Role::admin());
   }

   /**
    * @param FunctionalTester $I
    * @throws JsonException
    */
   public function createForProjectWithoutTemplate(FunctionalTester $I)
   {
      $dto = new DTO();
      $dto->name = $I->faker()->name;

      $I->sendPOST($this->url, json_encode($dto, JSON_THROW_ON_ERROR | JSON_THROW_ON_ERROR, 512));
      $I->seeResponseCodeIs(201);
      $I->seeResponseIsMatching(Conclusion::class);

      $I->seeDispatchedDomainEvent(ConclusionCreated::class);
   }

   /**
    * @param FunctionalTester $I
    * @throws JsonException
    */
   public function createForProjectWithTemplate(FunctionalTester $I)
   {
      $id = $I->haveTemplate([
         'Name' => 'test',
         'Paragraphs' => [
            [
               'Name' => 'p1',
               'Certificates' => [
                  'Certificate 1',
                  'Certificate 2',
               ],
               'BlockKind' => Kind::TEXT,
               'Children' => [
                  [
                     'Name' => 'p1.1',
                     'BlockKind' => Kind::DICT,
                     'Dictionaries' => [
                        'declarant.id',
                        'declarant.name',
                     ],
                  ]
               ],
            ],
         ],
      ]);

      $dto = new DTO();
      $dto->template_id = $id;
      $dto->name = $I->faker()->name;
      $I->sendPOST($this->url, json_encode($dto, JSON_THROW_ON_ERROR, 512));
      $I->seeResponseCodeIs(201);
      $I->seeResponseMatchesJsonType([
         'id' => 'string',
         'author' => ['id' => 'integer:=' . $this->user->getId()],
      ], '$.data');
      $I->seeDispatchedDomainEvent(ConclusionCreated::class);

      $response = json_decode($I->grabResponse(), true, 512, JSON_THROW_ON_ERROR)['data'];


      /** @var Conclusion[] $conclusions */
      $conclusions = $I->grabEntitiesFromRepository(Conclusion::class, ['id' => $response['id']]);
      $I->assertCount(1, $conclusions);

      $conclusion = $conclusions[0];

      $I->amGoingTo('Количество параграфов у заключения верно в бд и в ответе');
      /** @var Paragraph[] $paragraphs */
      $paragraphs = $I->grabEntitiesFromRepository(Paragraph::class);
      $I->assertCount(2, $paragraphs);
      $I->assertCount(2, $response['paragraphs']);

      $I->amGoingTo('Иерархия параграфов не нарушена');
      $rootParagraph = $conclusion->getRootParagraphs()[0];
      $I->assertInstanceOf(Paragraph::class, $rootParagraph);
      $childParagraph = $conclusion->getRootParagraphs()[0]->getChildren()[0];
      $I->assertInstanceOf(Paragraph::class, $childParagraph);
      $I->assertInstanceOf(Block::class, $childParagraph->getBlocks()->first());
      $I->assertEquals($conclusion, $childParagraph->getConclusion());

      $I->amGoingTo('Родительский блок создался верно');
      $rootParagraphBlock = $rootParagraph->getBlocks()->first();
      $I->assertInstanceOf(Block::class, $rootParagraphBlock);
      $I->assertEquals(Kind::TEXT, $rootParagraphBlock->getKind()->getValue());
      $I->assertNotEmpty((string)$rootParagraphBlock->getFilePath());
      $I->assertEquals($I->getLatestDocumentCreatedPath(), (string)$rootParagraphBlock->getFilePath());


      $I->amGoingTo('Дочерний создался верно');
      $childParagraphBlock = $childParagraph->getBlocks()->first();
      $I->assertEquals(Kind::DICT, $childParagraphBlock->getKind()->getValue());

      $I->amGoingTo('У родительского параграфа добавлены сертификаты');
      $I->assertCount(2, $rootParagraph->getCertificates());

      $I->amGoingTo('У дочернего параграфа добавлены словарные значения');
      /** @var DictionaryRepository $dictionaryRepository */
      $dictionaryRepository = $I->grabService(DictionaryRepository::class);
      $dictionaries = $dictionaryRepository->findByBlock($childParagraphBlock);
      $I->assertCount(2, $dictionaries);
   }

}
