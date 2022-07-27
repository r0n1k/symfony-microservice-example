<?php


namespace App\Domain\Common\Service;


use App\Domain\Common\Flusher;
use App\Domain\Project\Entity\Conclusion\Conclusion;
use App\Domain\Project\Entity\Conclusion\Paragraph\Block\Block;
use App\Domain\Project\Entity\Conclusion\Paragraph\Block\Kind;
use App\Domain\Project\Entity\Conclusion\Paragraph\Block\Order as BlockOrder;
use App\Domain\Project\Entity\Conclusion\Paragraph\Block\State;
use App\Domain\Project\Entity\Conclusion\Paragraph\Order;
use App\Domain\Project\Entity\Conclusion\Paragraph\Paragraph;
use App\Domain\Project\Entity\Conclusion\Paragraph\Title;
use App\Domain\Project\Entity\Conclusion\TemplateId;
use App\Domain\Project\Entity\Dictionary\Dictionary;
use App\Domain\Project\Entity\Dictionary\Key;
use App\Domain\Project\Entity\Users\User\Certificate\Certificate;
use App\Domain\Project\Entity\Users\User\Certificate\Scope;
use App\Domain\Project\Repository\Certificate\CertificateRepository;
use App\Domain\Project\Repository\Conclusion\Paragraph\Block\BlockRepository;
use App\Domain\Project\Repository\Conclusion\Paragraph\ParagraphRepository;
use App\Domain\Project\Repository\Dictionary\DictionaryRepository;
use App\Domain\Template\Entity;
use Doctrine\Common\Collections\Collection;

class TemplateBootstrapper
{

   /**
    * @var ParagraphRepository
    */
   private ParagraphRepository $paragraphs;
   /**
    * @var BlockRepository
    */
   private BlockRepository $blocks;
   /**
    * @var BlockFilePathResolverInterface
    */
   private BlockFilePathResolverInterface $pathResolver;
   /**
    * @var CertificateRepository
    */
   private CertificateRepository $certificates;
   /**
    * @var DictionaryRepository
    */
   private DictionaryRepository $dictionaryRepository;
    /**
     * @var Flusher
     */
    private Flusher $flusher;

    public function __construct(ParagraphRepository $paragraphs,
                               BlockRepository $blocks,
                               CertificateRepository $certificates,
                               DictionaryRepository $dictionaryRepository,
                               BlockFilePathResolverInterface $pathResolver,
                                Flusher $flusher)
   {
      $this->paragraphs = $paragraphs;
      $this->blocks = $blocks;
      $this->pathResolver = $pathResolver;
      $this->certificates = $certificates;
      $this->dictionaryRepository = $dictionaryRepository;
       $this->flusher = $flusher;
   }

   /**
    * @param Entity\Template $template
    * @param Conclusion $conclusion
    */
   public function bootstrap(Entity\Template $template, Conclusion $conclusion)
   {
      $conclusion->setTemplateId(new TemplateId($template->getId()));
      /** @var Paragraph[] $result */
       $i = 0;
      foreach ($template->getRootParagraphs() as $root) {
         $this->bootstrapParagraphs($root, $conclusion, null, $i);
         $i ++;
      }
   }

   private function bootstrapParagraphs(Entity\TemplateParagraph\TemplateParagraph $templateParagraph,
                                        Conclusion $conclusion,
                                        Paragraph $parent = null, $i = 0): ?Paragraph
   {
      $paragraphId = $this->paragraphs->nextId();
      $paragraphTitle = new Title($templateParagraph->getTitle()->getValue());
      if ($parent instanceof Paragraph) {
          if($this->existsWithSameNameAndParent($parent->getChildren()->getValues(), $templateParagraph)){
              return null;
          }
         $conclusionParagraph = $parent->addChild($paragraphId, $paragraphTitle, new Order($i));
      } else {
          if($this->existsWithSameNameAndParent($conclusion->getRootParagraphs()->getValues(), $templateParagraph)){
              return null;
          }
         $conclusionParagraph = $conclusion->addParagraph($paragraphId, $paragraphTitle, new Order($i));
      }

      //$conclusionParagraph->setOrder(Order::of($templateParagraph->getOrder()));

      if ($templateParagraph->getBlockKind()) {

         $blockId = $this->blocks->nextId();
         $blockKind = $templateParagraph->getBlockKind()->getValue() === Kind::TEXT ? Kind::text() : Kind::dict();
         $blockState = State::initial();
         $order = new BlockOrder(0);
         $block = $conclusionParagraph->addBlock($blockId, $blockKind, $blockState, $order);

         if ($blockKind->isText()) {
            $block->setFilePath($this->pathResolver->resolve($block));
         }
      }

      if ($templateCertificates = $templateParagraph->getCertificates()) {
         foreach ($templateCertificates as $templateCertificate) {
            $certificate = $this->certificates->findByScope(Scope::of($templateCertificate->getName()));
            if (!$certificate instanceof Certificate) {
               $certificate = new Certificate(
                  $this->certificates->nextId(),
                  Scope::of($templateCertificate->getName())
               );
               $this->certificates->add($certificate);
            }
            $conclusionParagraph->addCertificate($certificate);
         }
      }

      $templateDictionaries = $templateParagraph->getDictionaries();
      if (!$templateDictionaries->isEmpty()) {
         if (!$conclusionParagraph->getBlocks()->first() instanceof Block ||
             !$conclusionParagraph->getBlocks()->first()->getKind()->isDict())
         {
            throw new \LogicException('Шаблон некорректен');
         }
         foreach ($templateDictionaries as $templateDictionary) {
            $dictionary = new Dictionary(
               $this->dictionaryRepository->nextId(),
               Key::of($templateDictionary->getDictionaryKey()),
               $conclusion->getProject(),
               $conclusionParagraph->getBlocks()->first(),
            );
            $this->dictionaryRepository->add($dictionary);
         }
      }

      // для правильной сортировки, т.к. при записи всех скопом она сломается, операция редкая и даже 1с юзер подождет
       $this->paragraphs->add($conclusionParagraph);
      $this->flusher->flush();
      $j = 0;
      foreach ($templateParagraph->getChildren() as $child) {
         $this->bootstrapParagraphs($child, $conclusion, $conclusionParagraph, $j);
         $j ++;
      }

      return $conclusionParagraph;
   }

    public function existsWithSameNameAndParent(array $bro, Entity\TemplateParagraph\TemplateParagraph $templateParagraph)
    {
        foreach ($bro as $paragraph){
            /** @var Paragraph $paragraph */
            if($paragraph->getTitle()->getValue() == $templateParagraph->getTitle()->getValue()){
                return true;
            }
        }
        return false;
    }

}
