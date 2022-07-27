<?php


namespace App\Domain\Template\UseCase\Delete;


use App\Domain\Common\Flusher;
use App\Domain\Template\Repository\TemplateRepository;

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

   public function handle(DTO $dto)
   {
      $template = $this->templates->get($dto->template_id);

      $this->templates->remove($template);
      $this->flusher->flush();
   }

}
