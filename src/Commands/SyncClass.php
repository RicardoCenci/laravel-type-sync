<?php

namespace RicardoFabris\LaravelTypeSync\Commands;

use Illuminate\Console\Command;
use RicardoFabris\LaravelTypeSync\Codegen\TSCodeGen;
use RicardoFabris\LaravelTypeSync\Codegen\Typescript\TSFile;

class SyncClass extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'typesync:one {className} {phpBaseNamespace} {tsOutDir}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Transform the input className into a interface if it implements TypescriptTranslatable';

    /**
     * Execute the console command.
     */
    public function handle()
    {

        $codegen = new TSCodeGen(
            $this->argument('tsOutDir'),
            $this->argument('phpBaseNamespace'),
        );


        [$classesToGenerate] = $codegen->generatePHPtoTSMap();

        $className = $this->argument('className');
        $className = str_replace('/', '\\', $className);

        $value = $classesToGenerate[$className] ?? null;
        
        if(!$value) {
            $this->error("Class $className not found");
            return;
        }
        list($reflectionClass, $tsFile) = $value;

        /**
         * @var \ReflectionClass $reflectionClass
         * @var TSFile $tsFile
         */
        $codegen->generateFromEntry($reflectionClass, $tsFile);
        $this->info("Generated $className");
    }
}
