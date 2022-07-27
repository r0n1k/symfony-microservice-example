<?php
/** @noinspection PhpUnused */

namespace App\Tests\Http\Conclusion\Paragraph\Block;
use App\Domain\Project\Entity\Conclusion\Conclusion;
use App\Domain\Project\Entity\Conclusion\Paragraph\Block\Block;
use App\Domain\Project\Entity\Conclusion\Paragraph\Paragraph;
use App\Domain\Project\Entity\Users\User\Role;
use App\Domain\Project\Event\Conclusion\Paragraph\Block\BlockDeleted;
use App\Tests\FunctionalTester;

class DeleteCest
{
   private Block $block;
   private ?Paragraph $paragraph;
   private ?Conclusion $conclusion;

   public function _before(FunctionalTester $I)
    {
       $this->block = $I->have(Block::class);
       $this->paragraph = $this->block->getParagraph();
       $this->conclusion = $this->paragraph->getConclusion();
       $I->amLoggedInWithRole(Role::admin());
    }

    // tests
    public function deleteBlock(FunctionalTester $I)
    {
       $I->seeInRepository(Paragraph::class, ['id' => $this->paragraph->getId()]);
       $I->assertCount(1, $this->paragraph->getBlocks());
       $I->assertInstanceOf(Block::class, $this->paragraph->getBlocks()[0]);
       $url = $I->grabRoute('conclusion.paragraph.block.delete', [
          'conclusion_id' => $this->conclusion->getId()->getValue(),
          'paragraph_id' => $paragraphId = $this->paragraph->getId()->getValue(),
          'block_id' => $blockId = $this->block->getId()->getValue(),
       ]);

       $I->sendDELETE($url);
       $I->seeResponseCodeIs(200);
       $I->seeDispatchedDomainEvent(BlockDeleted::class);
       $I->clearDispatchedDomainEvents();


       $I->seeInRepository(Paragraph::class, ['id' => $paragraphId]);
       $I->dontSeeInRepository(Block::class, ['id' => $blockId]);
    }
}
