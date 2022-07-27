<?php /** @noinspection PhpUnused */

namespace App\Tests\Services;

use App\Domain\Project\Entity\Conclusion\Paragraph\Block\Block;
use App\Domain\Project\Entity\Conclusion\Paragraph\Block\Kind;
use App\Domain\Project\Entity\Conclusion\Paragraph\Paragraph;
use App\Domain\Project\Entity\Users\User\Role;
use App\Tests\FunctionalTester;

class CreateBlockDocumentSubscriberCest
{


   public function _before(FunctionalTester $I)
   {
      $I->amLoggedInWithRole(Role::admin());
   }

   public function test(FunctionalTester $I)
   {
      /** @var Paragraph $paragraph */
      $paragraph = $I->have(Paragraph::class);
      $I->dontSeeInRepository(Block::class);
      $I->seeInRepository(Paragraph::class, ['id' => $paragraph->getId()]);

      $url = $I->grabRoute('conclusion.paragraph.block.create', [
         'conclusion_id' => $paragraph->getConclusion()->getId()->getValue(),
         'paragraph_id' => $paragraph->getId()->getValue(),
      ]);

      $I->sendPOST($url, [
         'kind' => Kind::TEXT,
      ]);
      $I->seeResponseCodeIs(201);

      $I->assertCount(1, $I->grabEntitiesFromRepository(Block::class));
      $I->grabEntityFromRepository(Paragraph::class, ['id' => $paragraph->getId()->getValue()]);
      $block = $paragraph->getBlocks()->first();
      $I->assertInstanceOf(Block::class, $block);

      // $I->resolveFilePath($block) не вернет путь тк нет онлиофиса
      // $I->assertEquals($I->getLatestDocumentCreatedPath(), $I->resolveFilePath($block)->getPath());
   }
}
