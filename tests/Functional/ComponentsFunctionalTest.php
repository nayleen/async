<?php

declare(strict_types = 1);

namespace Nayleen\Async;

use Amp\PHPUnit\AsyncTestCase;
use Nayleen\Async\Test\TestComponent;
use Nayleen\Async\Test\TestKernel;

/**
 * @internal
 */
final class ComponentsFunctionalTest extends AsyncTestCase
{
    /**
     * @test
     */
    public function shutdown_runs_shutdown_on_components(): void
    {
        $components = new Components([new TestComponent()]);

        $kernel = new TestKernel($components);
        $components->shutdown($kernel);

        self::assertTrue($kernel->log->hasDebugThatMatches('/Shutting down Dependency/'));
    }
}
