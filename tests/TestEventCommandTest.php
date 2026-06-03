<?php

namespace Turbo124\Beacon\Tests;

use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Test;
use Turbo124\Beacon\Collector;

class TestEventCommandTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        config([
            'beacon.enabled' => true,
            'beacon.api_key' => 'test-key',
        ]);
    }

    public static function modeProvider(): array
    {
        return [
            'default batch' => [[], 'batch', 'batch'],
            'fire option' => [['--fire' => true], 'fire', 'fire'],
            'batch option' => [['--batch' => true], 'batch', 'batch'],
            'queue option' => [['--queue' => true], 'queue', 'queue'],
        ];
    }

    #[Test]
    #[DataProvider('modeProvider')]
    public function itEmitsATestEventUsingTheSelectedMode(array $options, string $expectedMethod, string $expectedMode): void
    {
        $collector = new FakeTestEventCollector();
        $this->app->instance('collector', $collector);

        $this->artisan('beacon:test-event', array_merge($options, [
            '--name' => 'demo.metric',
            '--value' => '2.5',
        ]))
            ->expectsOutput("Beacon test event emitted using {$expectedMode} mode.")
            ->assertExitCode(0);

        $this->assertSame($expectedMethod, $collector->method);
        $this->assertSame('demo.metric', $collector->metric->name);
        $this->assertSame(2.5, $collector->metric->metric);
    }

    #[Test]
    public function itRejectsMultipleModes(): void
    {
        $collector = new FakeTestEventCollector();
        $this->app->instance('collector', $collector);

        $this->artisan('beacon:test-event', [
            '--fire' => true,
            '--batch' => true,
        ])
            ->expectsOutput('Choose only one of --fire, --batch, or --queue.')
            ->assertExitCode(1);

        $this->assertNull($collector->method);
    }

    #[Test]
    public function itRejectsNonNumericValues(): void
    {
        $collector = new FakeTestEventCollector();
        $this->app->instance('collector', $collector);

        $this->artisan('beacon:test-event', [
            '--value' => 'not-a-number',
        ])
            ->expectsOutput('The --value option must be numeric.')
            ->assertExitCode(1);

        $this->assertNull($collector->method);
    }

    #[Test]
    public function itRejectsDisabledBeaconConfig(): void
    {
        config(['beacon.enabled' => false]);

        $collector = new FakeTestEventCollector();
        $this->app->instance('collector', $collector);

        $this->artisan('beacon:test-event')
            ->expectsOutput('Beacon is disabled or missing an API key.')
            ->assertExitCode(1);

        $this->assertNull($collector->method);
    }
}

class FakeTestEventCollector extends Collector
{
    public $metric;

    public ?string $method = null;

    public function create($metric)
    {
        $this->metric = $metric;

        return $this;
    }

    public function send()
    {
        $this->method = 'fire';

        return $this;
    }

    public function batch()
    {
        $this->method = 'batch';

        return $this;
    }

    public function queue()
    {
        $this->method = 'queue';

        return $this;
    }
}
