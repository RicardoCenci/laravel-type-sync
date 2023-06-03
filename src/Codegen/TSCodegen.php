<?php

namespace RicardoFabris\LaravelTypeSync\Codegen;

use RicardoFabris\LaravelTypeSync\Codegen\PHP\PHPSource;
use RicardoFabris\LaravelTypeSync\Codegen\Typescript\TSEnum;
use RicardoFabris\LaravelTypeSync\Codegen\Typescript\TSFile;
use RicardoFabris\LaravelTypeSync\Codegen\Typescript\TSInterface;
use RicardoFabris\LaravelTypeSync\Contracts\TypescriptTranslatable;
use RicardoFabris\LaravelTypeSync\Enums\PHPBuiltInTypes;
use RicardoFabris\LaravelTypeSync\Enums\TypescriptBuiltInTypes;

class TSCodeGen
{
    private array $interfacesToClassMap = [];
    private PHPSource $source;

    public function __construct(
        private string $tsOutDir,
        string $fullPhpNamespace
    ) {
        $this->tsOutDir = resource_path($this->tsOutDir);
        $this->source = new PHPSource($fullPhpNamespace);
    }

    public function generatePHPtoTSMap(): array
    {
        [$map, $errors] = $this->getPHPToTsFilesMap();
        $this->interfacesToClassMap = $map;
        return [$this->interfacesToClassMap, $errors];
    }

    public function generateFromEntry(\ReflectionClass $reflectionClass, TSFile $tsFile): string
    {
        if($reflectionClass->isEnum()){
            $this->makeEnum($reflectionClass, $tsFile);
        }else{
            $this->makeInterface($reflectionClass, $tsFile);
        }

        $tsFile->saveTo($this->tsOutDir);

        return $tsFile->getName();
    }

    private function makeInterface(\ReflectionClass $reflectionClass, TSFile $tsFile){
        $interface = new TSInterface($reflectionClass, $this);

        foreach($reflectionClass->getProperties() as $property) {
            $interface->addProperty($property);
        }

        $tsFile->addTSType($interface);
    
        return $tsFile;
    }

    private function makeEnum(\ReflectionEnum $reflectionClass, TSFile $tsFile){
        $enum = new TSEnum($reflectionClass, $this);

        foreach($reflectionClass->getCases() as $case) {
            $enum->addCase($case);
        }

        $tsFile->addTSType($enum);
        
        return $tsFile;
    }

    public function generateTSFromMap()
    {
        foreach ($this->interfacesToClassMap as $phpClassName => [$reflectionClass, $tsFile]) {
            $this->generateFromEntry($reflectionClass, $tsFile);
        }
    }

    public function getType(\ReflectionProperty $property)
    {
        $type = $property->getType();
        $phpType = $type->getName();

        if($type->isBuiltin()) {
            $tsType = PHPBuiltInTypes::tryFrom($phpType);
            return $tsType ? $tsType->getTypescriptType() : TypescriptBuiltInTypes::ANY->value;
        }

        if(!class_exists($phpType)) {
            return TypescriptBuiltInTypes::ANY->value;
        }


        [$reflectionClass, $tsFile] = $this->interfacesToClassMap[$phpType] ?? [ null, null];

        if(!$reflectionClass || !$tsFile) {
            return TypescriptBuiltInTypes::ANY->value;
        }

        /**
         * @var \ReflectionClass $reflectionClass
         * @var TSFile $tsFile
         */

        if(!$reflectionClass->implementsInterface(TypescriptTranslatable::class)) {
            return TypescriptBuiltInTypes::ANY->value;
        }

        return [$reflectionClass, $tsFile];
    }

    private function generatePHPMapFromFiles(array $files)
    {
        $map = [];
        $errors = [];
        
        foreach($files as $fileName) {
            try {

                [$reflectionClass, $tsFile] = $this->generateEntryFromFilename($fileName);
                /**
                 * @var \ReflectionClass $reflectionClass
                 * @var TSFile $tsFile
                 */
                
                if(!$reflectionClass->implementsInterface(TypescriptTranslatable::class)) {
                    continue;
                }

                $map[$reflectionClass->getName()] = [$reflectionClass, $tsFile];

            } catch (\Throwable $th) {

                $errors[] = $th;

            }
        }
        return [$map, $errors];
    }

    private function getPHPToTsFilesMap()
    {
        $files = $this->source->getSourceFiles();
        return $this->generatePHPMapFromFiles($files);
    }

    private function generateEntryFromFilename(string $filename): array
    {

        $namespace = $this->source->fileNameToNamespace($filename);


        try {

            $reflectionClass =  new \ReflectionClass($namespace);

            if($reflectionClass->isEnum()){
                $reflectionClass = new \ReflectionEnum($namespace);
            }
        } catch (\Throwable $th) {

            throw new \Exception("Could not get reflection class for $namespace. \n\t\t".$th->getMessage(). ': ' .$th->getLine());

        }

        $ts = new TSFile($this->source->getRelativePath($filename));

        return [$reflectionClass, $ts];
    }

    public function getSource(): PHPSource
    {
        return $this->source;
    }
}
