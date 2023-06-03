<?php

namespace RicardoFabris\LaravelTypeSync\Codegen\Utils;

class Files
{
    public static function ensureDirectoryExist(string $dir)
    {
        if(is_dir($dir)) {
            return;
        }
        mkdir($dir, 0777, true);
    }
    public static function glob(string $pattern, array $files = [])
    {
        $globFiles = glob($pattern, GLOB_NOSORT | GLOB_BRACE);

        foreach($globFiles as $fileName) {
            if(is_dir($fileName)) {
                $files = [...static::glob($fileName . "/*", $files)];
                continue;
            }
            $extension = pathinfo($fileName, PATHINFO_EXTENSION);

            if($extension !== 'php') {
                continue;
            }

            $files[] = $fileName;
        }

        return $files;
    }

}
