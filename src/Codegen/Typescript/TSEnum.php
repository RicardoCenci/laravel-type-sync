<?php

namespace RicardoFabris\LaravelTypeSync\Codegen\Typescript;

use RicardoFabris\LaravelTypeSync\Codegen\TSCodeGen;

class TSEnum
{

    private array $cases = [];
    public function __construct(
        protected \ReflectionEnum $reflectionEnum,
        protected TSCodeGen $tsCodeGenInstance
    ) {
    }

    public function getImports(){
        return [];
    }

    public function addCase(\ReflectionClassConstant $case){
        $this->cases[] = $case;
    }


    public function __toString()
    {
        $name = $this->reflectionEnum->getShortName();

        $classDocComment = $this->reflectionEnum->getDocComment();
        $enum = '';

        if($classDocComment) {
            $enum .= $classDocComment;
            $enum .= "\n";
        }
       
        
        $enum .= "export declare enum {$name} {\n";

        foreach ($this->cases as $case) {
            $enum .= "\t";

            $docComment = $case->getDocComment();
            if($docComment) {
                $enum .= $docComment;
                $enum .= "\n\t";
            }

            $enum .= $case->getName();

            $caseValue = $case->getValue();

            

            if(!empty($caseValue->value)){
                
                $enum .= " = ";

                $value = $caseValue->value;

                $enum .= is_string($value) ? $this->addQuotes($value) : $value;

            }

            $enum .= ",\n";
        }

        $enum .= "}\n\n";

        return $enum;
    }

    private function addQuotes($value){
        return "'{$value}'";
    }
}
