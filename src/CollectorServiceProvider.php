<?php

namespace Turbo124\Beacon;

use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Support\ServiceProvider;
use Turbo124\Beacon\Collector;
use Turbo124\Beacon\Commands\ForceSend;
use Turbo124\Beacon\Commands\PurgeAnalytics;
use Turbo124\Beacon\Jobs\BatchMetrics;

class CollectorServiceProvider extends ServiceProvider
{
    /**
     * Bootstrap the application services.
     */
    public function boot()
    {

        if ($this->app->runningInConsole()) {
            $this->publishes([
                __DIR__.'/../config/beacon.php' => config_path('beacon.php'),
            ], 'config');

        }

        if ($this->app->runningInConsole()) {
            $this->commands([
                ForceSend::class,
                PurgeAnalytics::class,
            ]);
        }
    }

    /**
     * Register the application services.
     */
    public function register()
    {
        // Automatically apply the package configuration
        $this->mergeConfigFrom(__DIR__.'/../config/beacon.php', 'beacon');

        // Register the main class to use with the facade
        // $this->app->singleton('collector', function () {
        //     return new Collector;
        // });

        $this->app->bind('collector', function (){
           return new Collector; 
        });

        if(config('beacon.enabled'))
        {
            /* Register the scheduler */
            $this->app->booted(function () {
                $schedule = app(Schedule::class);
                $schedule->job(new BatchMetrics())->everyFiveMinutes();
            });
        }
    }
}
