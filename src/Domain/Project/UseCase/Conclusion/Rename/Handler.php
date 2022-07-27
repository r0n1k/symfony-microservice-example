<?php


namespace App\Domain\Project\UseCase\Conclusion\Rename;


use App\Domain\Common\Flusher;
use App\Domain\Project\Entity\Conclusion\Conclusion;
use App\Domain\Project\Repository\Conclusion\ConclusionRepository;
use App\Domain\Project\Entity\Conclusion\Title;

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

   public function handle(DTO $dto): Conclusion
   {
      $conclusion = $this->conclusions->get($dto->conclusion_id);
      $conclusion->setTitle(new Title($dto->name));

      $this->conclusions->add($conclusion);
      $this->flusher->flush();

      return $conclusion;
   }

}
