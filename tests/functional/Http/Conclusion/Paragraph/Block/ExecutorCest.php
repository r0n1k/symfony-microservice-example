<?php
/** @noinspection PhpUnused */

namespace App\Tests\Http\Conclusion\Paragraph\Block;
use App\Domain\Project\Entity\Conclusion\Conclusion;
use App\Domain\Project\Entity\Conclusion\Paragraph\Block\Block;
use App\Domain\Project\Entity\Conclusion\Paragraph\Paragraph;
use App\Domain\Project\Entity\Users\User\Role;
use App\Domain\Project\Event\Conclusion\Paragraph\Block\BlockChanged;
use App\Domain\Project\UseCase\Conclusion\Paragraph\Block\SetExecutor;
use App\Domain\Project\Entity\Project\Project;
use App\Domain\Project\Entity\Users\User\User;
use App\Tests\FunctionalTester;

class ExecutorCest
{
   /** @var Project */
   private Project $project;

   /** @var Conclusion */
   private Conclusion $conclusion;

   /** @var Paragraph */
   private Paragraph $paragraph;

   /** @var Block */
   private Block $block;
   /**
    * @var string
    */
   private string $url;

   public function _before(FunctionalTester $I)
   {
      $this->block = $I->have(Block::class);
      $this->paragraph = $this->block->getParagraph();
      $this->conclusion = $this->paragraph->getConclusion();
      $this->project = $this->conclusion->getProject();
      $this->url = $I->grabRoute('conclusion.paragraph.block.executor', [
         'conclusion_id' => $this->conclusion->getId(),
         'paragraph_id' => $this->paragraph->getId(),
         'block_id' => $this->block->getId(),
      ]);

      $I->haveInRepository($this->project);
   }

   public function setExecutorByPermittedUser(FunctionalTester $I)
   {
      /** @var User $executor */
      $executor = $I->have(User::class);
      $I->assignUserToProject($executor, $this->project, 'main_expert');
      $I->amLoggedInAs($executor);

      $I->amGoingTo('Assign me as a block executor');
      $dto = new SetExecutor\DTO();
      $dto->user_id = $executor->getId()->getValue();

      $I->seeInRepository(Project::class, ['id' => $this->project->getId()]);

      $I->sendPATCH($this->url, json_encode($dto, JSON_THROW_ON_ERROR, 512));
      $I->seeResponseCodeIs(200);
      $I->seeDispatchedDomainEvent(BlockChanged::class, $this->block);
      $I->clearDispatchedDomainEvents();



      $I->amGoingTo('Assign an another user in the project as a block executor while block already has executor');
      $anotherUser = $I->have(User::class);
      $I->assignUserToProject($anotherUser, $this->project, 'expert');
      $I->sendPATCH($this->url, json_encode($dto, JSON_THROW_ON_ERROR, 512));
      $I->seeResponseCodeIs(400);
   }


   /**
    * @param FunctionalTester $I
    */
   public function setInvalidExecutor(FunctionalTester $I)
   {
      /** @var User $executor */
      $executor = $I->amLoggedInWithRole(Role::expert());

      $I->amGoingTo('Assing me as a block executor while I am not assigned to the project');
      $dto = new SetExecutor\DTO();
      $dto->user_id = $executor->getId()->getValue();

      $I->sendPATCH($this->url, json_encode($dto, JSON_THROW_ON_ERROR, 512));
      $I->seeResponseCodeIs(403);
   }
}
