<?php

namespace RicardoFabris\LaravelTypeSync\Commands;

use Illuminate\Console\Command;
use Reflection;
use ReflectionClass;
use RicardoFabris\LaravelTypeSync\Codegen\TSCodeGen;
use RicardoFabris\LaravelTypeSync\Codegen\Typescript\TSFile;

class SyncDirectory extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'typesync:all {phpBaseNamespace} {tsOutDir}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Transform all classes that implements TypescriptTranslatable into interfaces';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->getOutput()->title('Generating TS files from PHP classes');

        $codegen = new TSCodeGen(
            $this->argument('tsOutDir'),
            $this->argument('phpBaseNamespace')
        );


        $this->comment('Searching for classes');

        [$classesToGenerate, $mapError] = $codegen->generatePHPtoTSMap();
        $nroOfClasses = count($classesToGenerate);

        if(count($mapError)) {
            foreach($mapError as $error) {
                $this->error($error->getMessage(). ' in ' . $error->getFile() . ':' . $error->getLine());
            }
        }
        $this->comment("Found $nroOfClasses classes");

        if ($nroOfClasses === 0) {
            $this->info('No classes found');
            return;
        }
        $progressBar = $this->getOutput()->createProgressBar($nroOfClasses);
        $progressBar->setFormat("%message%\n %current%/%max% [%bar%] %percent:3s%%");
        $progressBar->setMessage("Starting...");
        $progressBar->start();
        $errors = [];
        $nroOfFiles = 0;

        $this->comment('Generating TS files');
        foreach ($classesToGenerate as $input) {

            list($reflectionClass, $tsFile) = $input;
            /**
             * @var TSFile $tsFile
             * @var \ReflectionClass $reflectionClass
             */

            $name = $reflectionClass->getName();
            $progressBar->setMessage("Generating $name");

            try {
                $codegen->generateFromEntry($reflectionClass, $tsFile);
                $nroOfFiles++;
            } catch (\Throwable $th) {
                $errors[] = [$name, $th];
            } finally {
                $progressBar->advance();
            }
        }
        $progressBar->finish();

        $this->newLine();
        $this->info("Generated $nroOfFiles files out of $nroOfClasses classes");

        $nroOfErrors = count($errors);
        if($nroOfErrors) {
            $this->error("There were {$nroOfErrors} errors: ");
            foreach($errors as $error) {
                list($name, $th) = $error;
                $this->error("Error in $name: " . $th->getMessage() . " in " . $th->getFile() . ":" . $th->getLine());
            }
        };


    }
}
