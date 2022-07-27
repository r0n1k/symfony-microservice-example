<?php


namespace App\Domain\Template;


use App\Domain\Common\Service\YamlParserInterface;
use App\Domain\Template\Entity\Id;
use App\Domain\Template\Entity\Template;
use App\Domain\Template\Entity\TemplateParagraph\TemplateBlockKind;
use App\Domain\Template\Entity\TemplateParagraph\TemplateParagraph;
use App\Domain\Template\Entity\TemplateParagraph\Title as ParagraphTitle;
use App\Domain\Template\Entity\Title as TemplateTitle;
use RuntimeException;
use Webmozart\Assert\Assert;

class TemplateFileRepository
{

   /**
    * @var YamlParserInterface
    */
   private YamlParserInterface $parser;

   public function __construct(YamlParserInterface $parser)
   {
      $this->parser = $parser;
   }

   public function findAll()
   {
      $basicTemplates = [];
      foreach (glob("{$this->basicTemplatesDirectory()}/*.yaml") as $basicTemplateFile) {
         $basicTemplates[] = $this->buildFromFile($basicTemplateFile);
      }
      return $basicTemplates;
   }

   public function find($id)
   {
      $id = strtoupper($id);
      $filePath = "{$this->basicTemplatesDirectory()}/{$id}.yaml";
      if (file_exists($filePath)) {
         return $this->buildFromFile($filePath);
      }

      return null;
   }

   protected function basicTemplatesDirectory(): string
   {
      return __DIR__ . '/BasicTemplates';
   }

   private function buildFromFile(string $filePath): Template
   {
      Assert::fileExists($filePath);
      $contents = $this->parser->parse(file_get_contents($filePath));
      Assert::isArray($contents);

      $id = preg_replace(':.*/([0-9a-fA-F]{8}-[0-9a-fA-F]{4}-[0-9a-fA-F]{4}-[0-9a-fA-F]{4}-[0-9a-fA-F]{12})\.yaml:', '$1', $filePath);
      return $this->templateFromArray($id, $contents);
   }

   private function templateFromArray($id, $contents): Template
   {
      if (!isset($contents['Name'])) {
         throw new RuntimeException('Wrong template: the Name field is not defined in a conclusion.');
      }
      $template = new Template(new Id($id), new TemplateTitle($contents['Name']), true);

      $this->handle($contents['Paragraphs'], $template);

      return $template;
   }

   private function handle($rawParagraphs, Template $template, TemplateParagraph $parent = null)
   {
      foreach ($rawParagraphs as $rawParagraph) {
          $blockKind = null;
         if (!isset($rawParagraph['Name'])) {
            throw new RuntimeException('Wrong template: the Name field is not defined in a paragraph');
         }

         if (isset($rawParagraph['BlockKind'])) {
            $blockKind = new TemplateBlockKind($rawParagraph['BlockKind']);
         }

         $title = new ParagraphTitle($rawParagraph['Name']);

         if ($parent instanceof TemplateParagraph) {
            $paragraph = $parent->addChild($title, null, $blockKind ?? null);
         } else {
            $paragraph = $template->addParagraph($title, null, $blockKind ?? null);
         }

         if (isset($rawParagraph['Certificates'])) {
            foreach ($rawParagraph['Certificates'] as $scope) {
               $paragraph->addCertificate($scope);
            }
         }

         if (isset($rawParagraph['Dictionaries'])) {
            foreach ($rawParagraph['Dictionaries'] as $dictionary) {
               $paragraph->addDictionary($dictionary);
            }
         }

         if (isset($rawParagraph['Children'])) {
            if (!is_array($rawParagraph['Children'])) {
               throw new RuntimeException('Wrong template: Children field should be array');
            }
            $this->handle($rawParagraph['Children'], $template, $paragraph);
         }
      }
   }

}
