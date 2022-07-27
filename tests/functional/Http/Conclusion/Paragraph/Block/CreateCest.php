<?php
/** @noinspection PhpUnused */

namespace App\Tests\Http\Conclusion\Paragraph\Block;
use App\Domain\Project\Entity\Conclusion\Conclusion;
use App\Domain\Project\Entity\Conclusion\Paragraph\Block\Block;
use App\Domain\Project\Entity\Conclusion\Paragraph\Block\Kind;
use App\Domain\Project\Entity\Conclusion\Paragraph\Paragraph;
use App\Domain\Project\Entity\Project\Project;
use App\Domain\Project\Entity\Users\User\Role;
use App\Domain\Project\Entity\Users\User\User;
use App\Domain\Project\Event\Conclusion\Paragraph\Block\BlockCreated;
use App\Domain\Project\Event\Conclusion\Paragraph\Block\BlockDeleted;
use App\Domain\Project\UseCase\Conclusion\Paragraph\Block\Create\DTO;
use App\Tests\FunctionalTester;

class CreateCest
{

   /** @var Project */
   private Project $project;

   /** @var Conclusion */
   private Conclusion $conclusion;

   /** @var Paragraph */
   private Paragraph $paragraph;
   /**
    * @var string
    */
   private string $url;

   public function _before(FunctionalTester $I)
   {
      /** @var Paragraph paragraph */
      $this->paragraph = $I->have(Paragraph::class);
      $this->conclusion = $this->paragraph->getConclusion();
      $this->project = $this->conclusion->getProject();

      $I->haveInRepository($this->project);
      $I->haveInRepository($this->conclusion);

      $I->seeInRepository(Conclusion::class, ['id' => $this->conclusion->getId()->getValue()]);
      $I->seeInRepository(Paragraph::class, ['id' => $this->paragraph->getId()->getValue()]);

      $this->url = $I->grabRoute('conclusion.paragraph.block.create', [
         'conclusion_id' => $this->conclusion->getId(),
         'paragraph_id' => $this->paragraph->getId(),
      ]);

      $I->amLoggedInWithRole(Role::admin());
   }

   // tests
   public function createWithValidData(FunctionalTester $I)
   {
      $dto = new DTO();
      $dto->kind = $I->faker()->randomElement([Kind::TEXT, Kind::DICT]);

      $data = json_encode($dto, JSON_THROW_ON_ERROR, 512);
      $I->sendPOST($this->url, $data);
      $I->seeResponseCodeIs(201);
      $blockId = json_decode($I->grabResponse(), true, 512, JSON_THROW_ON_ERROR)['data']['id'];

      $I->seeInRepository(Block::class, ['id' => $blockId]);
      $I->seeDispatchedDomainEvent(BlockCreated::class);
      $I->clearDispatchedDomainEvents();

   }

   public function createWithInvalidData(FunctionalTester $I)
   {
      $dto = new DTO();
      $dto->kind = $I->faker()->text(5);
      $data = json_encode($dto, JSON_THROW_ON_ERROR, 512);

      $I->sendPOST($this->url, $data);
      $I->seeResponseCodeIs(400);
      $I->dontSeeDispatchedDomainEvent(BlockCreated::class);
      $I->clearDispatchedDomainEvents();
   }

   public function createdWithKindTextHasFilePath(FunctionalTester $I)
   {
      $dto = new DTO();
      $dto->kind = Kind::TEXT;
      $data = json_encode($dto, JSON_THROW_ON_ERROR, 512);

      $I->sendPOST($this->url, $data);
      $I->seeResponseCodeIs(201);
      $I->seeResponseMatchesJsonType([
         'id' => 'integer',
         'kind' => 'string:=' . Kind::TEXT,
      ], '$.data');

      $blockId = json_decode($I->grabResponse(), true, 512, JSON_THROW_ON_ERROR)['data']['id'];
      /** @var Block $block */
      $block = $I->grabEntityFromRepository(Block::class, ['id' => $blockId]);

      $I->assertNotEmpty($fp = $block->getFilePath());
      $I->seeInRepository(Block::class, ['filePath.path' => (string)$fp]);
      $I->seeDispatchedDomainEvent(BlockCreated::class);
      $I->clearDispatchedDomainEvents();
   }

}
