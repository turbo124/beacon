<?php

namespace Turbo124\Beacon;

use Turbo124\Beacon\Collector;
use Turbo124\Beacon\Jobs\BatchMetrics;
use Illuminate\Support\ServiceProvider;
use Turbo124\Beacon\Commands\ForceSend;
use Turbo124\Beacon\Commands\CountMetrics;
use Illuminate\Console\Scheduling\Schedule;
use Turbo124\Beacon\Commands\PurgeAnalytics;

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
                CountMetrics::class,
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

        $this->app->bind('collector', function (){
           return new Collector; 
        });

        if(config('beacon.enabled'))
        {
            /* Register the scheduler */
            $this->app->booted(function () {
                $schedule = app(Schedule::class);
                $schedule->job(new BatchMetrics())->everyFiveMinutes()->withoutOverlapping()->name('beacon-batch-job')->onOneServer();
            });
        }
    }
}
