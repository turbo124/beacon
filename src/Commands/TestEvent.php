<?php

namespace Turbo124\Beacon\Commands;

use Illuminate\Console\Command;
use Turbo124\Beacon\Collector;
use Turbo124\Beacon\ExampleMetric\GenericGauge;

class TestEvent extends Command
{
    protected $signature = 'beacon:test-event
        {--fire : Send the test metric immediately via Generator::fire()}
        {--batch : Store the test metric for the batch sender}
        {--queue : Dispatch the test metric onto the queue}
        {--name=beacon.test_event : Metric name to emit}
        {--value=1 : Numeric metric value to emit}';

    protected $description = 'Emit a Beacon test event using fire, batch, or queue mode.';

    public function handle()
    {
        if (!config('beacon.enabled') || empty(config('beacon.api_key'))) {
            $this->error('Beacon is disabled or missing an API key.');

            return self::FAILURE;
        }

        $mode = $this->resolveMode();

        if ($mode === null) {
            return self::FAILURE;
        }

        $name = (string) $this->option('name');

        if ($name === '') {
            $this->error('The --name option must not be empty.');

            return self::FAILURE;
        }

        $value = $this->option('value');

        if (!is_numeric($value)) {
            $this->error('The --value option must be numeric.');

            return self::FAILURE;
        }

        $metric = new GenericGauge();
        $metric->name = $name;
        $metric->metric = $value + 0;

        $collector = $this->collector()->create($metric);

        switch ($mode) {
            case 'fire':
                $collector->send();
                break;
            case 'batch':
                $collector->batch();
                break;
            case 'queue':
                $collector->queue();
                break;
        }

        $this->info("Beacon test event emitted using {$mode} mode.");

        return self::SUCCESS;
    }

    private function resolveMode(): ?string
    {
        $selected = array_values(array_filter(
            ['fire', 'batch', 'queue'],
            fn($mode) => (bool) $this->option($mode)
        ));

        if (count($selected) > 1) {
            $this->error('Choose only one of --fire, --batch, or --queue.');

            return null;
        }

        return $selected[0] ?? 'batch';
    }

    protected function collector(): Collector
    {
        return app('collector');
    }
}
