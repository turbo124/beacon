<?php

namespace Turbo124\Collector;

use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Support\ServiceProvider;
use Turbo124\Collector\Collector;
use Turbo124\Collector\Jobs\BatchMetrics;

class CollectorServiceProvider extends ServiceProvider
{
    /**
     * Bootstrap the application services.
     */
    public function boot()
    {

        if ($this->app->runningInConsole()) {
            $this->publishes([
                __DIR__.'/../config/collector.php' => config_path('collector.php'),
            ], 'config');

        }

        \Illuminate\Support\Facades\Event::listen(
            Turbo124\Collector\Events\MetricRegistered::class,
            MyListener::class
        );
    }

    /**
     * Register the application services.
     */
    public function register()
    {
        // Automatically apply the package configuration
        $this->mergeConfigFrom(__DIR__.'/../config/collector.php', 'collector');

        // Register the main class to use with the facade
        $this->app->singleton('collector', function () {
            return new Collector;
        });

        /* Register the scheduler */
        $this->app->booted(function () {
            $schedule = app(Schedule::class);
            $schedule->job(new BatchMetrics())->everyFiveMinutes();
        });

    }
}
