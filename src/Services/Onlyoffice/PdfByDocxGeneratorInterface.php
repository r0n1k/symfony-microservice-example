<?php


namespace App\Services\Onlyoffice;


use App\Domain\Project\Entity\Conclusion\Conclusion;

interface PdfByDocxGeneratorInterface
{
   public function generate(Conclusion $conclusion): ?string;
}
