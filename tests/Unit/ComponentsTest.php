<?php

declare(strict_types = 1);

namespace Nayleen\Async;

use Amp\PHPUnit\AsyncTestCase;
use Nayleen\Async\Component\Bootstrapper;
use Nayleen\Async\Test\TestComponent;
use Nayleen\Async\Test\TestKernel;

/**
 * @internal
 * @small
 */
final class ComponentsTest extends AsyncTestCase
{
    /**
     * @test
     */
    public function adds_dependencies(): void
    {
        $component = new TestComponent();
        $components = new Components([$component]);

        self::assertEquals([new Bootstrapper(), $component], iterator_to_array($components));
    }

    /**
     * @test
     */
    public function boot_runs_boot_on_components(): void
    {
        $components = new Components([new TestComponent()]);

        $kernel = new TestKernel($components);
        $components->boot($kernel);

        self::assertTrue($kernel->log->hasDebugThatContains('Booting TestComponent'));
    }

    /**
     * @test
     */
    public function prevents_duplicates(): void
    {
        $component = new Bootstrapper();
        $components = new Components([$component, $component]);

        self::assertSame([$component], iterator_to_array($components));
    }

    /**
     * @test
     */
    public function shutdown_runs_shutdown_on_components(): void
    {
        $components = new Components([new TestComponent()]);

        $kernel = new TestKernel($components);
        $components->shutdown($kernel);

        self::assertTrue($kernel->log->hasDebugThatContains('Shutting down TestComponent'));
    }
}
