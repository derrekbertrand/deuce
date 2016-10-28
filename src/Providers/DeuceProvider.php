<?php

namespace DerrekBertrand\Deuce\Providers;

use Illuminate\Support\ServiceProvider;

class DeuceProvider extends ServiceProvider
{
    /**
     * Bootstrap the application services.
     *
     * @return void
     */
    public function boot()
    {
        $this->publishes([
            __DIR__.'/../../config/deuce.php' => config_path('deuce.php')
        ]);

        if($this->app->runningInConsole())
        {
            $this->commands([
                \DerrekBertrand\Deuce\Commands\DumpCommand::class,
                \DerrekBertrand\Deuce\Commands\LoadCommand::class,
            ]);
        }
    }

    /**
     * Register the application services.
     *
     * @return void
     */
    public function register()
    {
        //
    }
}
