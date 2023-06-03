<?php

namespace RicardoFabris\LaravelTypeSync\Codegen\PHP;

use RicardoFabris\LaravelTypeSync\Codegen\Utils\Files;

class PHPSource
{
    private string $baseNamespace;

    public function __construct(string $phpBaseNamespace) {
        $this->baseNamespace = str_replace(config('typesync.app_base_namespace', 'App'),'',$phpBaseNamespace);
    }

    public function getSourceFiles()
    {
        return Files::glob(app_path($this->baseNamespace . "*"));
    }

    public function fileNameToNamespace(string $fileName)
    {
        $fileName = str_replace(app_path(), config('typesync.app_base_namespace', 'App'), $fileName);
        $fileName = str_replace('.php', "", $fileName);
        return str_replace('/', '\\', $fileName);
    }

    public function getRelativePath(string $filePath)
    {
        $filePath = str_replace(app_path(), "", $filePath);
        $filePath = str_replace('.php', "", $filePath);
        $filePath = str_replace($this->baseNamespace, '', $filePath);
        return $filePath;
    }
}
