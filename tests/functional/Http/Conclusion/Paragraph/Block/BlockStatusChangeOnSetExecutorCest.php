<?php /** @noinspection PhpIllegalPsrClassPathInspection */
/** @noinspection PhpUnused */
/** @noinspection JsonEncodingApiUsageInspection */
/** @noinspection PhpFieldAssignmentTypeMismatchInspection */

namespace App\Tests\Http\Conclusion\Paragraph\Block;
use App\Domain\Project\Entity\Conclusion\Paragraph\Block\Block;
use App\Domain\Project\Entity\Conclusion\Paragraph\Block\State;
use App\Domain\Project\Entity\Users\User\Role;
use App\Domain\Project\Entity\Users\User\User;
use App\Tests\FunctionalTester;

class BlockStatusChangeOnSetExecutorCest
{
   private Block $block;
   private User $user;

   public function _before(FunctionalTester $I)
    {
       $this->user = $I->amLoggedInWithRole(Role::admin());
       $this->block = $I->have(Block::class);
    }

    public function testIfStatusChanges(FunctionalTester $I)
    {
       $route = $I->grabRoute('conclusion.paragraph.block.executor', [
          'conclusion_id' => $this->block->getParagraph()->getConclusion()->getId(),
          'paragraph_id' => $this->block->getParagraph()->getId(),
          'block_id' => $this->block->getId(),
       ]);

       $I->sendPATCH($route, json_encode(['user_id' => $this->user->getId()->getValue()]));
       $I->seeResponseCodeIsSuccessful();

       $this->block = $I->grabEntityFromRepository(Block::class, ['id' => $this->block->getId()]);
       $I->assertEquals(State::WORK_IN_PROGRESS, $this->block->getState()->getValue());
    }
}
