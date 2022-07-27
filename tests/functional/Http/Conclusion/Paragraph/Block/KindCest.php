<?php
/** @noinspection PhpUnused */

namespace App\Tests\Http\Conclusion\Paragraph\Block;
use App\Domain\Project\Entity\Conclusion\Conclusion;
use App\Domain\Project\Entity\Conclusion\Paragraph\Block\Block;
use App\Domain\Project\Entity\Conclusion\Paragraph\Block\Kind;
use App\Domain\Project\Entity\Conclusion\Paragraph\Paragraph;
use App\Domain\Project\Entity\Project\Project;
use App\Domain\Project\Entity\Users\User\Role;
use App\Domain\Project\Event\Conclusion\Paragraph\Block\BlockChanged;
use App\Domain\Project\UseCase\Conclusion\Paragraph\Block\SetKind;
use App\Tests\FunctionalTester;


class KindCest
{
   private Project $project;
   private Conclusion $conclusion;
   private Paragraph $paragraph;
   private Block $block;
   private string $url;

   public function _before(FunctionalTester $I)
   {
      $this->block = $I->have(Block::class, ['kind' => Kind::text()]);
      $this->paragraph = $this->block->getParagraph();
      $this->conclusion = $this->paragraph->getConclusion();
      $this->project = $this->conclusion->getProject();

      $this->url = $I->grabRoute('conclusion.paragraph.block.kind', [
         'conclusion_id' => $this->conclusion->getId()->getValue(),
         'paragraph_id' => $this->paragraph->getId()->getValue(),
         'block_id' => $this->block->getId()->getValue(),
      ]);
   }

   public function setKindByMainExpert(FunctionalTester $I)
   {
      $user = $I->amLoggedIn();
      $I->assignUserToProject($user, $this->project, 'main_expert');

      $blockId = $this->block->getId()->getValue();
      $paragraphId = $this->block->getParagraph()->getId()->getValue();
      $conclusionId = $this->conclusion->getId()->getValue();
      $I->seeInRepository(Block::class, ['id' => $blockId]);
      $I->seeInRepository(Paragraph::class, ['id' => $paragraphId]);
      $I->seeInRepository(Conclusion::class, ['id' => $conclusionId]);

      $I->amGoingTo('Successfully switch kind of a block');

      $dto = new SetKind\DTO();
      $dto->kind = Kind::DICT;
      $I->sendPATCH($this->url, json_encode($dto, JSON_THROW_ON_ERROR, 512));
      $I->seeResponseCodeIs(200);
      $I->seeInRepository(Block::class, ['id' => $blockId]);
      $I->seeInRepository(Paragraph::class, ['id' => $paragraphId]);
      $I->seeInRepository(Conclusion::class, ['id' => $conclusionId]);
      $I->seeDispatchedDomainEvent(BlockChanged::class);
      $I->clearDispatchedDomainEvents();


      $this->url = $I->grabRoute('conclusion.paragraph.block.kind', [
         'conclusion_id' => $this->conclusion->getId()->getValue(),
         'paragraph_id' => $this->paragraph->getId()->getValue(),
         'block_id' => $this->block->getId()->getValue(),
      ]);

      $dto = new SetKind\DTO();
      $dto->kind = Kind::TEXT;
      $I->sendPATCH($this->url, json_encode($dto, JSON_THROW_ON_ERROR, 512));
      $I->seeResponseCodeIs(200);
      $I->seeInRepository(Block::class, ['id' => $blockId]);
      $I->seeInRepository(Paragraph::class, ['id' => $paragraphId]);
      $I->seeInRepository(Conclusion::class, ['id' => $conclusionId]);
      $I->seeDispatchedDomainEvent(BlockChanged::class);
      $I->clearDispatchedDomainEvents();


//      $I->amGoingTo('Get error while trying to set the same kind to a block');
//      $I->sendPATCH($this->url, json_encode($dto, JSON_THROW_ON_ERROR, 512));
//      $I->seeResponseCodeIs(400);
//      $I->seeInRepository(Block::class, ['id' => $blockId]);
//      $I->seeInRepository(Paragraph::class, ['id' => $paragraphId]);
//      $I->seeInRepository(Conclusion::class, ['id' => $conclusionId]);
//      $I->dontSeeDispatchedDomainEvent(BlockChanged::class);
//      $I->clearDispatchedDomainEvents();


      $I->amGoingTo('Get error while trying to set the wrong kind to a block');
      $dto->kind = 'incorrect kind';
      $I->sendPATCH($this->url, json_encode($dto, JSON_THROW_ON_ERROR, 512));
      $I->seeResponseCodeIs(400);
      $I->seeInRepository(Block::class, ['id' => $blockId]);
      $I->seeInRepository(Paragraph::class, ['id' => $paragraphId]);
      $I->seeInRepository(Conclusion::class, ['id' => $conclusionId]);
      $I->dontSeeDispatchedDomainEvent(BlockChanged::class);
      $I->clearDispatchedDomainEvents();
   }


   public function updatedWithKindTextHasFilePath(FunctionalTester $I)
   {
      $I->amLoggedInWithRole(Role::admin());
      if ($this->block->getKind()->getValue() !== Kind::DICT) {
         $this->block->setDictKind();
         $this->block->setFilePath(null);
         $I->haveInRepository($this->block);
      }

      $I->seeInRepository(Block::class, ['id' => $this->block->getId(), 'kind' => Kind::DICT, 'filePath.path' => null]);

      $dto = new SetKind\DTO();
      $dto->kind = Kind::TEXT;
      $data = json_encode($dto, JSON_THROW_ON_ERROR, 512);

      $I->sendPATCH($this->url, $data);
      $I->seeResponseCodeIs(200);
      $I->seeResponseMatchesJsonType([
         'id' => 'integer',
         'kind' => 'string:=' . Kind::TEXT,
      ], '$.data');
      $I->seeDispatchedDomainEvent(BlockChanged::class);


      $blockId = json_decode($I->grabResponse(), true, 512, JSON_THROW_ON_ERROR)['data']['id'];
      /** @var Block $block */
      $block = $I->grabEntityFromRepository(Block::class, ['id' => $blockId]);

      $I->assertNotEmpty($block->getFilePath());
   }

}
