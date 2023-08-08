<?php

declare(strict_types = 1);

namespace Nayleen\Async;

use Amp\PHPUnit\AsyncTestCase;
use Monolog\Handler\TestHandler;
use Monolog\Logger;
use Nayleen\Async\Component\DependencyProvider;
use Nayleen\Async\Test\TestComponent;

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
        $logger = new Logger('test');
        $logger->pushHandler($handler = new TestHandler());

        $components = new Components([
            new TestComponent(),
            DependencyProvider::create([
                'async.logger.debug' => $logger,
            ]),
        ]);

        $kernel = new Kernel($components);
        $components->shutdown($kernel);

        self::assertTrue($handler->hasDebugThatMatches('/Shutting down Dependency/'));
    }
}
