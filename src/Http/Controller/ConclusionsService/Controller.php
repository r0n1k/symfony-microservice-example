<?php /** @noinspection PhpUnused */


namespace App\Http\Controller\ConclusionsService;


use App\Domain\Common\Flusher;
use App\Domain\Project\Entity\Conclusion\Paragraph\Block\Block;
use App\Domain\Project\Repository\Conclusion\Paragraph\Block\BlockRepository;
use App\Domain\Project\Entity\Conclusion\Paragraph\Block\FilePath;
use App\Services\Dictionary\BlockToHtmlConverterInterface;
use App\Services\Onlyoffice\FilePathKeyGenerator;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Routing\Annotation\Route;

class Controller
{

   /**
    * @var BlockToHtmlConverterInterface
    */
   private BlockToHtmlConverterInterface $htmlFetcher;
   /**
    * @var EntityManagerInterface
    */
   private EntityManagerInterface $em;
   /**
    * @var BlockRepository
    */
   private BlockRepository $blocks;
   /**
    * @var FilePathKeyGenerator
    */
   private FilePathKeyGenerator $keyGenerator;
   /**
    * @var Flusher
    */
   private Flusher $flusher;

   public function __construct(BlockToHtmlConverterInterface $htmlFetcher,
                               EntityManagerInterface $em,
                               BlockRepository $blocks,
                               Flusher $flusher,
                               FilePathKeyGenerator $keyGenerator)
   {
      $this->htmlFetcher = $htmlFetcher;
      $this->em = $em;
      $this->blocks = $blocks;
      $this->keyGenerator = $keyGenerator;
      $this->flusher = $flusher;
   }

   /**
    * @param Request $request
    * @Route(path="/hook/document-saved", methods={"POST"})
    */
   public function handle(Request $request)
   {
      $path = $request->query->get('path', null);
      if (empty($path)) {
         throw new BadRequestHttpException('`path` query parameter is not passed');
      }
      $block = $this->blocks->findByPathKey($path);

      if (!$block instanceof Block) {
         throw new NotFoundHttpException("Block with a path `{$path}` is not found");
      }

      $html = $this->htmlFetcher->convert($block);
      $block->setHtml($html->html);
      $block->setPreviewHtml($html->preview);
      /** @var FilePath $fp */
      $fp = $block->getFilePath();
      $key = $this->keyGenerator->generate();
      $block->setFilePath(new FilePath($fp->getPath(), $key));
      $this->em->persist($block);
      $this->flusher->flush();
   }

   /**
    * @Route(path="/hook/document-path", methods={"GET"})
    * @param Request $request
    * @return string|null
    */
   public function getPathByKey(Request $request)
   {
      $key = $request->query->get('key');
      $block = $this->blocks->findByPathKey($key);
      if (!$block) {
         throw new NotFoundHttpException();
      }
      return $block->getFilePath()->getPath();
   }

}
