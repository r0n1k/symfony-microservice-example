<?php


namespace App\Domain\Project\UseCase\Conclusion\SetState;


use App\Domain\Common\Flusher;
use App\Domain\Project\Entity\Conclusion\State;
use App\Domain\Project\Repository\Conclusion\ConclusionRepository;

class Handler
{
   /**
    * @var ConclusionRepository
    */
   private ConclusionRepository $conclusions;
   /**
    * @var Flusher
    */
   private Flusher $flusher;

   public function __construct(ConclusionRepository $conclusions, Flusher $flusher)
   {
      $this->conclusions = $conclusions;
      $this->flusher = $flusher;
   }

   public function handle(DTO $dto) {
      $conclusion = $this->conclusions->get($dto->conclusion_id);
      $conclusion->setState(new State($dto->state));
      $this->flusher->flush();
      return $conclusion;
   }

}
