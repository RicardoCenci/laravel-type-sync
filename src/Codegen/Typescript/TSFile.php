<?php

namespace RicardoFabris\LaravelTypeSync\Codegen\Typescript;

use RicardoFabris\LaravelTypeSync\Codegen\Utils\Files;

class TSFile
{
    protected array $imports = [];
    protected array $tsTypes = [];

    public function __construct(
        protected string $name
    ) {
    }

    public function addImport(string $name, string $from)
    {
        $this->imports[$from][] = $name;
    }

    public function addTSType(TSInterface | TSEnum $obj){
        $this->tsTypes[] = $obj;
        $this->imports = array_merge($this->imports, $obj->getImports());
    }

    public function getName()
    {
        return $this->name;
    }

    public function getOutputFileName(string $dir)
    {
        return $dir . '\\' . $this->name . "Type.d.ts";
    }

    private function getImports()
    {
        $header = '';
        foreach ($this->imports as $from => $imports) {
            $header .= "import { ";
            $header .= implode(', ', $imports);
            $header .= " } from '../{$from}';\n";
        }

        return $header === '' ? '' : $header . "\n\n";
    }

    public function saveTo(string $tsDir)
    {
        $filePath = $tsDir . '\\' .$this->name . 'Type.d.ts';
        $pathInfo = pathinfo($filePath);

        Files::ensureDirectoryExist($pathInfo['dirname']);

        return file_put_contents($filePath, strval($this));
    }

    public function __toString()
    {
        $file = $this->getImports();

        foreach ($this->tsTypes as $type) {
            $file .= $type;
            $file .= "\n";
        }
        return $file;
    }
}
