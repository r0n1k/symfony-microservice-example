<?php


namespace App\Domain\Common\Service;


use App\Domain\Project\Entity\Conclusion;
use App\Domain\Project\Entity\Conclusion\Paragraph\Paragraph;
use App\Domain\Project\Repository\Dictionary\DictionaryRepository;
use App\Domain\Template\Entity\Id;
use App\Domain\Template\Entity\Template;
use App\Domain\Template\Entity\TemplateParagraph\TemplateBlockKind;
use App\Domain\Template\Entity\TemplateParagraph\TemplateParagraph;
use App\Domain\Template\Entity\TemplateParagraph\Title as ParagraphTitle;
use App\Domain\Template\Entity\Title;
use App\Domain\Template\Repository\TemplateParagraph\TemplateParagraphRepository;

class ConclusionToTemplateMapper
{

   private TemplateParagraphRepository $paragraphs;
   /**
    * @var DictionaryRepository
    */
   private DictionaryRepository $dictionaries;

   public function __construct(TemplateParagraphRepository $paragraphs, DictionaryRepository $dictionaries)
   {
      $this->paragraphs = $paragraphs;
      $this->dictionaries = $dictionaries;
   }

   public function map(Conclusion\Conclusion $conclusion): Template
   {
      $template = new Template(Id::next(), new Title(''), false);

      $conclusionParagraphs = $conclusion->getParagraphs();
      $conclusionRootParagraphs = $conclusionParagraphs->filter(
         static function (Paragraph $paragraph) {
            return !$paragraph->getParent() instanceof Paragraph;
         }
      )->toArray();

      $this->fillTemplate($template, $conclusionRootParagraphs);
      return $template;
   }

   /**
    * @param Template $template
    * @param Paragraph[] $conclusionParagraphs
    * @param TemplateParagraph|null $parent
    */
   protected function fillTemplate(Template $template,
                                   Iterable $conclusionParagraphs,
                                   TemplateParagraph $parent = null)
   {

      foreach ($conclusionParagraphs as $conclusionParagraph) {
         $templateParagraphTitle = new ParagraphTitle($conclusionParagraph->getTitle()->getValue());
         $templateParagraphId = $this->paragraphs->nextId();
         $conclusionBlockKind = $conclusionParagraph->getBlocks()->first() ?
            new TemplateBlockKind($conclusionParagraph->getBlocks()->first()->getKind()->getValue()) :
            null;

         if ($parent instanceof TemplateParagraph) {
            $templateParagraph = $parent->addChild($templateParagraphTitle, $templateParagraphId, $conclusionBlockKind);
         } else {
            $templateParagraph = $template->addParagraph($templateParagraphTitle, $templateParagraphId, $conclusionBlockKind);
         }

         $templateParagraph->setOrder($conclusionParagraph->getOrder()->getValue());

         foreach ($conclusionParagraph->getCertificates() as $certificate) {
            $templateParagraph->addCertificate($certificate->getScope()->getValue());
         }

         if ($conclusionBlock = $conclusionParagraph->getBlocks()->first()) {
            foreach ($this->dictionaries->findByBlock($conclusionBlock) as $dictionary) {
               $templateParagraph->addDictionary($dictionary->getKey()->getValue());
            }
         }


         $this->fillTemplate($template, $conclusionParagraph->getChildren(), $templateParagraph);
      }
   }

}
