<?php
namespace App\Services\Resumable;


use App\Services\SiteEnvResolver;
use Dilab\Network\SimpleRequest;
use Dilab\Network\SimpleResponse;
use Dilab\Resumable;

class ResumableFacade
{
    private $hostResolver;

    public function __construct(SiteEnvResolver $hostResolver)
    {
        $this->hostResolver = $hostResolver;
    }

    /**
     * @param $uploadFolder
     *
     * @return string|null
     */
    public function getUploadedFile(string $savePath, $fileName = null) {
        /*if ($this->hostResolver->resolve()) {
            $savePath = "{$this->hostResolver->resolve()}/$uploadFolder";
        } else {
            $savePath = "localhost/$uploadFolder";
        }*/

        $request = new SimpleRequest();
        $response = new SimpleResponse();

        $resumable = new Resumable($request, $response);

        $resumable->deleteTmpFolder = true;
        $resumable->tempFolder = '/tmp/resumable_uploads/';
        $resumable->uploadFolder = $savePath;

        $dir = $resumable->tempFolder;
        if (!is_dir($dir) && !mkdir($dir , 0777, true) && !is_dir($dir)) {
            throw new \RuntimeException(sprintf('Directory "%s" was not created', $dir));
        }

        $dir = $resumable->uploadFolder;
        if (!is_dir($dir) && !mkdir($dir , 0777, true) && !is_dir($dir)) {
            throw new \RuntimeException(sprintf('Directory "%s" was not created', $dir));
        }

        $resumable->process();

        if (!$resumable->isUploadComplete()) {
            return null;
        }
        $newFilePath = $savePath.'/'.$fileName;
        $resumable->moveUploadedFile($resumable->getFilepath(), $newFilePath);

        return $newFilePath;
    }

}
