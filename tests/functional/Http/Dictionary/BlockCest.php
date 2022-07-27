<?php /** @noinspection JsonEncodingApiUsageInspection */
/** @noinspection DuplicatedCode */
/** @noinspection PhpIllegalPsrClassPathInspection */
/** @noinspection PhpFieldAssignmentTypeMismatchInspection */

/** @noinspection PhpUnused */

namespace App\Tests\Http\Dictionary;

use App\Domain\Project\Entity\Conclusion\Conclusion;
use App\Domain\Project\Entity\Conclusion\Paragraph\Block\Block;
use App\Domain\Project\Entity\Conclusion\Paragraph\Block\Kind;
use App\Domain\Project\Entity\Dictionary\Dictionary;
use App\Domain\Project\Entity\Project\Project;
use App\Domain\Project\Entity\Users\User\Role;
use App\Domain\Project\UseCase\Dictionary\SetBlocksDictionaries\DTO;
use App\Domain\Project\UseCase\Project\Upsert\Handler;
use App\Tests\FunctionalTester;
use App\Tests\Helper\MockClasses\TestProjectUpsertHandler;
use JsonException;
use Symfony\Component\DependencyInjection\ContainerInterface;

class BlockCest
{
   private Block $block;
   private Project $project;
   private Dictionary $blockDict;
   private Dictionary $projectDict;
   private Dictionary $anotherProjectDict;
   /**
    * @var Conclusion
    */
   private Conclusion $conclusion;

   public function _before(FunctionalTester $I)
   {
      $container = $I->grabService(ContainerInterface::class);
      $container->set(Handler::class, $container->get(TestProjectUpsertHandler::class));
      $this->block = $I->have(Block::class, ['kind' => Kind::dict()]);
      $this->conclusion = $this->block->getParagraph()->getConclusion();
      $this->project = $this->block->getParagraph()->getConclusion()->getProject();
      $this->projectDict = $I->have(Dictionary::class, [
         'project' => $this->project,
         'key' => 'project.1',
         'value' => $I->faker()->text(20),
      ]);
      $this->blockDict = $I->have(Dictionary::class, [
         'project' => $this->project,
         'block' => $this->block,
         'key' => $this->projectDict->getKey(),
      ]);
      $this->anotherProjectDict = $I->have(Dictionary::class, [
         'project' => $this->project,
         'value' => $I->faker()->text(20),
         'key' => 'project.2'
      ]);
      $I->amLoggedInWithRole(Role::admin());
   }


   /**
    * @param FunctionalTester $I
    * @throws JsonException
    */
   public function testCorrectFetch(FunctionalTester $I)
   {
      $url = $I->grabRoute('conclusion.get', [
         'conclusion_id' => $this->conclusion->getId(),
      ]);

      $I->sendGET($url);
      $I->seeResponseCodeIs(200);
      $I->seeResponseJsonMatchesJsonPath('data.paragraphs[0].blocks[0].dictionaries');
      $response = json_decode($I->grabResponse(), true, 512, JSON_THROW_ON_ERROR)['data'];
      $dictionaries = $response['paragraphs'][0]['blocks'][0]['dictionaries'];

      $I->assertCount(1, $dictionaries);
      $I->assertEquals($this->blockDict->getKey()->getValue(), $dictionaries[0]['key']);
   }

   /**
    * @param FunctionalTester $I
    * @throws JsonException
    */
   public function testCorrectDictionaryValueOverride(FunctionalTester $I)
   {
      $I->have(Dictionary::class, [
         'project' => $this->project,
         'block' => $this->block,
         'key' => $this->anotherProjectDict->getKey(),
         'value' => $overrideValue = $I->faker()->text(20),
      ]);

      $getConclusionUrl = $I->grabRoute('conclusion.get', ['conclusion_id' => $this->conclusion->getId()]);
      $I->sendGET($getConclusionUrl);
      $I->seeResponseCodeIs(200);
      $response = $I->grabResponse();
      $parsedResponse = json_decode($response, true, 512, JSON_THROW_ON_ERROR)['data'];

      $parsedDictionaries = $parsedResponse['paragraphs'][0]['blocks'][0]['dictionaries'];
      $I->assertCount(2, $parsedDictionaries);
      foreach ($parsedDictionaries as $parsedDictionary) {
         if ($parsedDictionary['key'] === $this->blockDict->getKey()->getValue()) {
            $I->assertEquals($this->projectDict->getValue(), $parsedDictionary['value']);
         } else if ($parsedDictionary['key'] === $this->anotherProjectDict->getKey()->getValue()) {
            $I->assertEquals($overrideValue, $parsedDictionary['value']);
         }
      }
   }

   /**
    * @param FunctionalTester $I
    * @throws JsonException
    */
   public function testSetNonExistentDictionary(FunctionalTester $I)
   {
      $url = $I->grabRoute('dictionary.block.set-dictionaries', [
         'block_id' => $this->block->getId(),
      ]);

      $dto = new DTO();
      $dto->keys = ['non-existent'];

      $I->sendPUT($url, json_encode($dto, JSON_THROW_ON_ERROR, 512));
      $I->seeResponseCodeIs(400);
   }

   /**
    * @param FunctionalTester $I
    * @throws JsonException
    */
   public function testSetBlockDictionaries(FunctionalTester $I)
   {
      $url = $I->grabRoute('dictionary.block.set-dictionaries', [
         'block_id' => $this->block->getId(),
      ]);

      $dto = new DTO();
      $dto->keys = [
         $this->anotherProjectDict->getKey()->getValue(),
      ];
      $requestBody = json_encode($dto, JSON_THROW_ON_ERROR, 512);

      $I->sendPUT($url, $requestBody);
      $I->seeResponseCodeIs(200);

      $I->dontSeeInRepository(Dictionary::class, ['block' => $this->block, 'key' => $this->blockDict->getKey()]);
      $I->seeInRepository(Dictionary::class, ['project' => $this->project, 'key' => $this->blockDict->getKey()]);
      $I->seeInRepository(Dictionary::class, ['block' => $this->block, 'key' => $this->anotherProjectDict->getKey()]);

      $I->amGoingTo('Verify if put operation is idempotent');
      $I->sendPUT($url, $requestBody);
      $I->seeResponseCodeIs(200);

      $I->dontSeeInRepository(Dictionary::class, ['block' => $this->block, 'key' => $this->blockDict->getKey()]);
      $I->seeInRepository(Dictionary::class, ['project' => $this->project, 'key' => $this->blockDict->getKey()]);
      $I->seeInRepository(Dictionary::class, ['block' => $this->block, 'key' => $this->anotherProjectDict->getKey()]);

   }
}
