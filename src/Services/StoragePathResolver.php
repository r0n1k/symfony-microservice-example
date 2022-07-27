<?php

namespace App\Services;


class StoragePathResolver
{

    const STORAGE_DIR = 'storage';
    private ?string $instanceName;
    private string $projectDir;

    public function __construct(string $instanceName, string $projectDir)
    {
        $this->instanceName = !empty($instanceName) ? $instanceName : 'localhost';
        $this->projectDir = $projectDir;
    }

    public function resolve(): string
    {
        return $this->projectDir .'/'.self::STORAGE_DIR.'/'. $this->instanceName . '/';
    }
}
