<?php


namespace App\Domain\Template\UseCase\Rename;


use App\Domain\Common\Flusher;
use App\Domain\Template\Entity\Template;
use App\Domain\Template\Entity\Title;
use App\Domain\Template\Repository\TemplateRepository;
use DomainException;

class Handler
{

   /**
    * @var TemplateRepository
    */
   private TemplateRepository $templates;
   /**
    * @var Flusher
    */
   private Flusher $flusher;

   public function __construct(TemplateRepository $templates, Flusher $flusher)
   {
      $this->templates = $templates;
      $this->flusher = $flusher;
   }

   public function handle(DTO $dto): Template
   {
      $template = $this->templates->get($dto->template_id);

      if ($template->getIsBasic()) {
         throw new DomainException('Cannot rename basic template');
      }

      $template->setTitle(new Title($dto->name));

//      $this->templates->add($template);
      $this->flusher->flush();

      return $template;
   }
}
