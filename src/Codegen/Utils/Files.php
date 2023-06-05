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


    public static function getRelativeImportPath(string $baseFilePath, string $targetFilePath): string
    {
        $baseDir = dirname($baseFilePath);
        $targetDir = dirname($targetFilePath);


        $commonPath = '';

        $basePathParts = explode('/', $baseDir);
        $targetPathParts = explode('/', $targetDir);

        $numOfParts = min(count($basePathParts), count($targetPathParts));


        for ($i = 0; $i < $numOfParts; $i++) {
            if($basePathParts[$i] !== $targetPathParts[$i]) {
                break;
            }

            $commonPath .= $basePathParts[$i] . '/';
        }

        $numOfBaseDirs = count($basePathParts) - $i;

        $relativeDirectory = $numOfBaseDirs == 0 ? './' : str_repeat('../', $numOfBaseDirs);

        $target = substr($targetDir, strlen($commonPath));

        $fileName = basename($targetFilePath);

        return str_replace('//', '/', $relativeDirectory . $target . '/' . $fileName);

    }

}
