<?php


namespace App\Tests\Helper\MockClasses;


use App\Domain\Common\Service\BlockFilePathResolverInterface;
use App\Domain\Project\Entity\Conclusion\Paragraph\Block\Block;
use App\Domain\Project\Entity\Conclusion\Paragraph\Block\FilePath;
use App\Tests\Helper\Functional;
use PHPUnit\Framework\Assert;

class TestFilePathResolver implements BlockFilePathResolverInterface
{

   /**
    * @var Functional
    */
   private ?Functional $tester = null;

   public function resolve(Block $block): ?FilePath
   {
      $path = "_block_{$block->getId()}";
      if ($this->tester instanceof Functional) {
         $this->tester->_setLatestResolvedBlockFilePath($path);
      }
      return new FilePath($path);
   }

   public function setTester(Functional $tester)
   {
      $this->tester = $tester;
   }

}
