<?php
/** @noinspection PhpUnused */

namespace App\Tests\Http\Conclusion\Paragraph\Block;
use App\Domain\Project\Entity\Conclusion\Conclusion;
use App\Domain\Project\Entity\Conclusion\Paragraph\Block\Block;
use App\Domain\Project\Entity\Conclusion\Paragraph\Block\State;
use App\Domain\Project\Entity\Conclusion\Paragraph\Paragraph;
use App\Domain\Project\Entity\Project\Project;
use App\Domain\Project\Event\Conclusion\Paragraph\Block\BlockChanged;
use App\Tests\FunctionalTester;
use App\Domain\Project\UseCase\Conclusion\Paragraph\Block\SetState;

class StateCest
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
      $this->block = $I->have(Block::class, ['state' => new State(State::WAITING_TO_START)]);
      $this->paragraph = $this->block->getParagraph();
      $this->conclusion = $this->paragraph->getConclusion();
      $this->project = $this->conclusion->getProject();

      $this->url = $I->grabRoute('conclusion.paragraph.block.state', [
         'conclusion_id' => $this->conclusion->getId(),
         'paragraph_id' => $this->paragraph->getId(),
         'block_id' => $this->block->getId(),
      ]);
   }

   public function stateTransitions(FunctionalTester $I)
   {
      $user = $I->amLoggedIn();
      $I->assignUserToProject($user, $this->project, 'main_expert');

      $dto = new SetState\DTO();

      $dto->new_state = State::WORK_IN_PROGRESS;
      $I->sendPATCH($this->url, json_encode($dto, JSON_THROW_ON_ERROR, 512));
      $I->seeResponseCodeIs(200);
      $I->seeResponseIsMatching(Block::class);
      $I->seeDispatchedDomainEvent(BlockChanged::class);
      $I->clearDispatchedDomainEvents();

      $dto->new_state = State::SENT_TO_REVIEW;
      $I->sendPATCH($this->url, json_encode($dto, JSON_THROW_ON_ERROR, 512));
      $I->seeResponseCodeIs(200);
      $I->seeResponseIsMatching(Block::class);
      $I->seeDispatchedDomainEvent(BlockChanged::class);
      $I->clearDispatchedDomainEvents();



      $dto->new_state = State::ON_REVIEW;
      $I->sendPATCH($this->url, json_encode($dto, JSON_THROW_ON_ERROR, 512));
      $I->seeResponseCodeIs(200);
      $I->seeResponseIsMatching(Block::class);
      $I->seeDispatchedDomainEvent(BlockChanged::class);
      $I->clearDispatchedDomainEvents();



      $dto->new_state = State::DECLINED;
      $I->sendPATCH($this->url, json_encode($dto, JSON_THROW_ON_ERROR, 512));
      $I->seeResponseCodeIs(200);
      $I->seeResponseIsMatching(Block::class);
      $I->seeDispatchedDomainEvent(BlockChanged::class);
      $I->clearDispatchedDomainEvents();



      $dto->new_state = State::WORK_IN_PROGRESS;
      $I->sendPATCH($this->url, json_encode($dto, JSON_THROW_ON_ERROR, 512));
      $I->seeResponseCodeIs(200);
      $I->seeResponseIsMatching(Block::class);
      $I->seeDispatchedDomainEvent(BlockChanged::class);
      $I->clearDispatchedDomainEvents();



      $dto->new_state = State::COMPLETED;
      $I->sendPATCH($this->url, json_encode($dto, JSON_THROW_ON_ERROR, 512));
      $I->seeResponseCodeIs(400);
      $I->dontSeeDispatchedDomainEvent(BlockChanged::class);
      $I->clearDispatchedDomainEvents();

   }

}
