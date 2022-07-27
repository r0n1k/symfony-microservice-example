<?php
namespace App\Services\ZipArchiver;

use App\Services\StoragePathResolver;
use League\Flysystem\FilesystemInterface;
use Ramsey\Uuid\Uuid;

class ArchiveBuilder
{
    const ARCHIVES_DIR = '/archives/';

    private $storage;
    private $fileName;
    private $path;
    /** @var $files array */
    private $files;
    private StoragePathResolver $storagePathResolver;

    public function __construct(FilesystemInterface $defaultStorage, StoragePathResolver $storagePathResolver)
    {
        $this->storage = $defaultStorage;
        $this->storagePathResolver = $storagePathResolver;
        $this->createDirectory();
        $this->setFileName();
        $this->setArchivePath();
    }

    private function createDirectory(){
        mkdir($this->storagePathResolver->resolve() . self::ARCHIVES_DIR, 0777);
        // TODO надо так, но тут ебанутые пермишны выставляются, разобратся в чем залупа
        //$this->storage->createDir(self::ARCHIVES_DIR, ['directory' => ['public' => true]]);
    }

    private function setFileName(){
        $name = Uuid::uuid4()->toString();
        $this->fileName = $name . '.zip';
    }

    private function setArchivePath(){
        $this->path = self::ARCHIVES_DIR . $this->fileName;
    }

    public function getFileName(){
        return $this->fileName;
    }

    public function addFile($filePath, $fileName){
        $filePath = $this->getAbsoluteFilePath($filePath);
        $this->files[$fileName] = $filePath;
    }

    private function getAbsoluteFilePath(string $filePath)
    {
        $storagePath = $this->storagePathResolver->resolve();
        return $storagePath . $filePath;
    }

    public function save(): string
    {
        $archivePath = $this->getAbsoluteFilePath($this->path);

        $spaceSeparatedCustomFiles = $this->createLinksWithCustomUserName();
        $this->buildArchie($archivePath, $spaceSeparatedCustomFiles);
        $this->removeFileLinks($spaceSeparatedCustomFiles);

        return $this->storagePathResolver->resolve() . $this->path;
    }

    private function createLinksWithCustomUserName(){
        // TODO функция занимается и созданием ссылок и формированием списка файлов, разделить
        $files = [];
        foreach ($this->files as $newName => $oldFullPath){
            $files[] = $this->createLinkWithCustomUserName($newName, $oldFullPath);
        }
        return $this->getStringWithAllFileNames($files);
    }

    private function createLinkWithCustomUserName(string $newFileName, string $oldFilePath){
        $dir = dirname($oldFilePath);
        $newFilePath = $dir . '/' . $newFileName;
        $newFilePath = escapeshellarg($newFilePath);
        exec("ln $oldFilePath $newFilePath");
        return $newFilePath;
    }

    private function getStringWithAllFileNames(array $files){
        return implode($files, ' ');
    }

    private function buildArchie(string $archiePath, string $spaceSeparatedFiles){
        exec("zip -j $archiePath $spaceSeparatedFiles", $output, $code);
        return $output;
    }

    private function removeFileLinks(string $spaceSeparatedLinks){
        exec("rm $spaceSeparatedLinks", $output);
        return $output;
    }
}
