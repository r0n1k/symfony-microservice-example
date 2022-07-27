<?php


namespace App\Services\Onlyoffice;


use App\Domain\Common\Service\BlockFilePathResolverInterface;
use App\Domain\Project\Entity\Conclusion\Conclusion;
use App\Domain\Project\Entity\Conclusion\Paragraph\Block\Block;
use App\Domain\Project\Entity\Conclusion\Paragraph\Block\FilePath;
use App\Domain\Project\Entity\Conclusion\Paragraph\Block\Kind;
use App\Domain\Project\Entity\Conclusion\Paragraph\Paragraph;
use App\Domain\Project\Entity\Project\Id;
use App\Domain\Project\Entity\Project\Project;
use App\Services\ErrorsCollection\ErrorsCollection;
use App\Services\SiteEnvResolver;
use Throwable;
use Webmozart\Assert\Assert;

/** @noinspection PhpUnused */
class BlockFilePathResolver implements BlockFilePathResolverInterface
{

   /**
    * @var DocumentCreatorInterface
    */
   private DocumentCreatorInterface $creator;
   /**
    * @var ErrorsCollection
    */
   private ErrorsCollection $errors;
   /**
    * @var string|null
    */
   private ?string $host;
   /**
    * @var FilePathKeyGenerator
    */
   private FilePathKeyGenerator $keyGenerator;

   public function __construct(DocumentCreatorInterface $creator,
                               SiteEnvResolver $resolver,
                               FilePathKeyGenerator $keyGenerator,
                               ErrorsCollection $errors)
   {
      $this->creator = $creator;
      $this->errors = $errors;
      $this->host = $resolver->resolve();
      $this->keyGenerator = $keyGenerator;
   }


   public function resolve(Block $block): ?FilePath
   {
      // тк нет онлиофиса
      return null;

      Assert::eq((string)$block->getKind(), Kind::TEXT, 'File path can be resolved against text blocks only');
      $filePath = $this->computePath($block);
      if ($this->host) {
         $filePath = "/{$this->host}{$filePath}";
      } else {
         $filePath = "/localhost{$filePath}";
      }
      try {
         $this->creator->create($filePath);
         $key = $this->keyGenerator->generate();
         return new FilePath($filePath, $key);
      } catch (Throwable $e) {
         $this->errors->add("Ошибка создания документа для рабочего блока: {$e->getMessage()}");
         return null;
      }
   }

   protected function computePath(Block $block)
   {
      Assert::notNull($block->getId());
      $paragraph = $block->getParagraph();
      Assert::isInstanceOf($paragraph, Paragraph::class);
      $conclusion = $paragraph->getConclusion();
      Assert::isInstanceOf($conclusion, Conclusion::class);
      $project = $conclusion->getProject();
      Assert::isInstanceOf($project, Project::class);
      $projectId = $project->getId();
      Assert::isInstanceOf($projectId, Id::class);

      return "/{$projectId}/{$conclusion->getId()}/{$paragraph->getId()}/{$block->getId()}.docx";
   }
}
