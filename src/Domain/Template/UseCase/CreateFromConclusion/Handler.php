<?php


namespace App\Domain\Template\UseCase\CreateFromConclusion;


use App\Domain\Common\Flusher;
use App\Domain\Common\Service\ConclusionToTemplateMapper;
use App\Domain\Project\Entity\Conclusion\TemplateId;
use App\Domain\Project\Repository\Conclusion\ConclusionRepository;
use App\Domain\Template\Entity\Template;
use App\Domain\Template\Entity\Title;
use App\Domain\Template\Repository\TemplateRepository;

class Handler
{

   /**
    * @var ConclusionRepository
    */
   private ConclusionRepository $conclusions;
   /**
    * @var ConclusionToTemplateMapper
    */
   private ConclusionToTemplateMapper $mapper;
   /**
    * @var Flusher
    */
   private Flusher $flusher;
   /**
    * @var TemplateRepository
    */
   private TemplateRepository $templates;

   public function __construct(ConclusionRepository $conclusions,
                               ConclusionToTemplateMapper $mapper,
                               Flusher $flusher,
                               TemplateRepository $templates)
   {
      $this->conclusions = $conclusions;
      $this->mapper = $mapper;
      $this->flusher = $flusher;
      $this->templates = $templates;
   }

   public function handle(DTO $dto): Template
   {
      $conclusion = $this->conclusions->get($dto->conclusion_id);
      $template = ($this->mapper->map($conclusion))
         ->setTitle(new Title($dto->name))
         ->setIsBasic(false);

      $this->templates->add($template);
      $conclusion->setTemplateId(new TemplateId($template->getId()), false);
      $this->flusher->flush();

      return $template;
   }

}
