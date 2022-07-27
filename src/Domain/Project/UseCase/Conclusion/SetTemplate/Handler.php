<?php


namespace App\Domain\Project\UseCase\Conclusion\SetTemplate;


use App\Domain\Common\Flusher;
use App\Domain\Common\Service\TemplateBootstrapper;
use App\Domain\Project\Entity\Conclusion\Conclusion;
use App\Domain\Project\Repository\Conclusion\ConclusionRepository;
use App\Domain\Template\Repository\TemplateRepository;
use DomainException;

class Handler
{

   /**
    * @var ConclusionRepository
    */
   private ConclusionRepository $conclusions;
   /**
    * @var TemplateRepository
    */
   private TemplateRepository $templates;
   /**
    * @var Flusher
    */
   private Flusher $flusher;
   /**
    * @var TemplateBootstrapper
    */
   private TemplateBootstrapper $mapper;


   public function __construct(ConclusionRepository $conclusions,
                               TemplateRepository $templates,
                               Flusher $flusher,
                               TemplateBootstrapper $mapper)
   {
      $this->conclusions = $conclusions;
      $this->templates = $templates;
      $this->flusher = $flusher;
      $this->mapper = $mapper;
   }

   public function handle(DTO $dto): Conclusion
   {
      $conclusion = $this->conclusions->get($dto->conclusion_id);
      $template = $this->templates->get($dto->template_id);

      $this->mapper->bootstrap($template, $conclusion);

      $this->flusher->flush();

      return $conclusion;
   }

}
