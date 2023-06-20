<?php

namespace RicardoFabris\LaravelTypeSync\Codegen\Typescript;

use RicardoFabris\LaravelTypeSync\Codegen\TSCodeGen;

abstract class TSType
{
    private array $imports = [];

    public function __construct(
        protected TSCodeGen $tsCodeGenInstance
    ) {
    }

    public function addImport(string $name, string $from)
    {
        $this->imports[$from][] = $name;
    }

    public function getImports()
    {
        return $this->imports;
    }

    abstract public function compile(): string;

    public function __toString()
    {
        return $this->compile();
    }

}
