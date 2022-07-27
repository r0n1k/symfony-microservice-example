<?php


namespace App\Services\Dictionary;


use App\Domain\Project\Entity\Conclusion\Paragraph\Block\Block;

interface BlockToHtmlConverterInterface
{

   public function convert(Block $block): HtmlDTO;

}
