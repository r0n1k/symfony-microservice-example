<?php


namespace App\Domain\Common\Service;


use App\Domain\Project\Entity\Conclusion\Paragraph\Block\Block;
use App\Domain\Project\Entity\Conclusion\Paragraph\Block\FilePath;

interface BlockFilePathResolverInterface
{

   public function resolve(Block $block): ?FilePath;

}
