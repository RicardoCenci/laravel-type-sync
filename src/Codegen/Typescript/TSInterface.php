<?php

namespace RicardoFabris\LaravelTypeSync\Codegen\Typescript;

use RicardoFabris\LaravelTypeSync\Codegen\TSCodeGen;

class TSInterface
{
    /**
     * @var \ReflectionProperty[]
     */
    private array $properties = [];
    private array $imports = [];

    public function __construct(
        protected \ReflectionClass $reflectionClass,
        protected TSCodeGen $tsCodeGenInstance
    ) {
    }

    public function addProperty(\ReflectionProperty $property)
    {
        $typeDef = $this->tsCodeGenInstance->getType($property);

        if (gettype($typeDef) != 'array') {
            $this->properties[] = [$property, $typeDef];
            return;
        }

        [$importReflectionClass, $importTsFile] = $typeDef;
        /** @var \ReflectionClass $reflectionClass */
        /** @var TSFile $tsFile */


        $this->addImport($importReflectionClass->getShortName(), $importTsFile->getName() . "Type");

        $this->properties[] = [$property, $importReflectionClass->getShortName()];
    }

    public function addImport(string $name, string $from)
    {
        $this->imports[$from][] = $name;
    }

    public function getImports()
    {
        return $this->imports;
    }

    public function __toString()
    {
        $name = $this->reflectionClass->getShortName();
        $classDocComment = $this->reflectionClass->getDocComment();
        $interface = '';

        if ($classDocComment) {
            $interface .= $classDocComment;
            $interface .= "\n";
        }

        $interface = "export interface {$name} {\n";

        foreach($this->properties as [$property, $type]) {
            /** @var \ReflectionProperty $property */

            $interface .= "\t";
            $docComment = $property->getDocComment();
            if($docComment) {
                $interface .= $docComment;
                $interface .= "\n\t";
            }
            $interface .= $property->getName();
            $interface .= $property->getType()->allowsNull() ? '?' : '';
            $interface .= ': ';
            $interface .= $type;
            $interface .= ";\n";

        }

        $interface .= "}\n";
        return $interface;
    }

}
