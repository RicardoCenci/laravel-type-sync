<?php

namespace RicardoFabris\LaravelTypeSync\Provider;

use Illuminate\Container\Container;
use Illuminate\Support\ServiceProvider;
use RicardoFabris\LaravelTypeSync\Commands\SyncClass;
use RicardoFabris\LaravelTypeSync\Commands\SyncDirectory;

// Namespace
class LaravelSyncProvider extends ServiceProvider
{

    const CONFIG_PATH = __DIR__ . '/../config/typesync.php';
    /**
     * Bootstrap services.
     *
     * @return void
     */
    public function boot()
    {
        if (!$this->app->runningInConsole()) {
            return;
        }
        
        $this->commands([
            SyncClass::class,
            SyncDirectory::class
        ]);

        $this->publishes([
            static::CONFIG_PATH => config_path('typesync.php'),
        ]);

    }

    public function register(){
        $this->mergeConfigFrom(
            static::CONFIG_PATH, 'typesync'
        );
    }
}
