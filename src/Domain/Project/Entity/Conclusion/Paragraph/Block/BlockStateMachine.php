<?php


namespace App\Domain\Project\Entity\Conclusion\Paragraph\Block;


use DomainException;

trait BlockStateMachine
{

   /**
    * С какого статуса на какие статусы можно перейти
    * @var array|array[]
    */
   private array $stateTransitions = [
      State::WAITING_TO_START => [State::WORK_IN_PROGRESS, State::SENT_TO_REVIEW, State::DELETED],
      State::WORK_IN_PROGRESS => [State::WAITING_TO_START, State::SENT_TO_REVIEW],
      State::SENT_TO_REVIEW => [State::ON_REVIEW, State::WAITING_TO_START],
      State::ON_REVIEW => [State::COMPLETED, State::DECLINED, State::DELETED],
      State::DECLINED => [State::WORK_IN_PROGRESS, State::ON_REVIEW, State::DELETED],
      State::COMPLETED => [State::ON_REVIEW, State::DELETED],
      State::DELETED => [
          State::WAITING_TO_START,
          State::ON_REVIEW,
          State::DECLINED,
          State::COMPLETED,
      ],
   ];

   /**
    * @param State $newState
    */
   public function setState(State $newState)
   {
      if (!array_key_exists((string)$this->state, $this->stateTransitions)) {
         throw new DomainException("Block has wrong state `{$this->state}`");
      }
      $availableStates = $this->stateTransitions[(string)$this->state];

      if (!in_array((string)$newState, $availableStates, true)) {
         throw new DomainException("Wrong state transit from {$this->state} to {$newState}");
      }

      $this->state = $newState;
   }

}
