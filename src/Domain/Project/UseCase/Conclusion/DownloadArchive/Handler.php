<?php


namespace App\Domain\Project\UseCase\Conclusion\DownloadArchive;


use App\Domain\Common\Flusher;
use App\Domain\Project\Entity\Conclusion\Conclusion;
use App\Domain\Project\Entity\Conclusion\Pdf\Pdf;
use App\Domain\Project\Entity\Conclusion\Pdf\Signature\Signature;
use App\Domain\Project\Repository\Conclusion\ConclusionRepository;
use App\Services\SiteEnvResolver;
use App\Services\ZipArchiver\ArchiveBuilder;

class Handler
{
    private ConclusionRepository $conclusions;
    private ArchiveBuilder $archiveBuilder;
    private SiteEnvResolver $siteEnvResolver;

    public function __construct(ConclusionRepository $conclusions, ArchiveBuilder $archiveBuilder, SiteEnvResolver $siteEnvResolver)
    {
        $this->conclusions = $conclusions;
        $this->archiveBuilder = $archiveBuilder;
        $this->siteEnvResolver = $siteEnvResolver;
    }

    public function handle(DTO $dto)
    {
        $conclusion = $this->conclusions->get($dto->conclusion_id);
        foreach ($conclusion->getPdfs()->toArray() as $pdf){
            /** @var Pdf $pdf */
            if($pdf->getId() != $dto->pdf_id){
                continue;
            }
            $pdfName = $pdf->getFileName() ?? basename($pdf->getPath());
            $pdfPath = $this->removeHostPrefix($pdf->getPath());
            $this->archiveBuilder->addFile($pdfPath, $pdfName);
            foreach ($pdf->getSignatures()->toArray() as $signature){
                /** @var Signature $signature */
                $sigData = $signature->getData();
                $sigFileName = $sigData['surname'] . ' ' . $sigData['givenName'] .' '. $signature->getId();
                $sigFilePath = $this->removeHostPrefix($signature->getPath());
                $this->archiveBuilder->addFile($sigFilePath, $sigFileName);
            }
        }
        $archivePath = $this->archiveBuilder->save();
        //var_dump($archivePath);
        $archiveName = $conclusion->getTitle()->getValue() ?? 'Заключение ' . $conclusion->getCreatedAt()->format("Y-m-d H:i:s");

        $mime = mime_content_type($archivePath);
        header('Content-Type: ' . ($mime ?? 'application/octet-stream'));
        header('Content-Disposition: attachment; filename="' . $archiveName . '"');

        readfile($archivePath);
        exit(0);
    }

    private function removeHostPrefix(string $filePath): string
    {
        return preg_replace("/^(\/)?{$this->siteEnvResolver->resolve()}/", '', $filePath);
    }

}
