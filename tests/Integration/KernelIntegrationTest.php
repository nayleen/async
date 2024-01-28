<?php

declare(strict_types = 1);

namespace Nayleen\Async;

use Amp\DeferredCancellation;
use Amp\PHPUnit\AsyncTestCase;
use Monolog\Handler\TestHandler;
use Monolog\Logger;
use Nayleen\Async\Component\DependencyProvider;

/**
 * @internal
 */
final class KernelIntegrationTest extends AsyncTestCase
{
    /**
     * @test
     */
    public function can_trap_signals(): void
    {
        $logger = new Logger('test');
        $logger->pushHandler($handler = new TestHandler());

        $cancellation = new DeferredCancellation();

        $kernel = new Kernel(
            [
                DependencyProvider::create([
                    Logger::class => $logger,
                ]),
            ],
            cancellation: $cancellation->getCancellation(),
        );
        $return = $kernel->run(function (Kernel $kernel) use ($cancellation): int {
            $kernel->loop()->defer(fn () => $cancellation->cancel());
            $kernel->trap(SIGINT);

            return 69;
        });

        self::assertNull($return);
        self::assertTrue($handler->hasInfoThatContains('Awaiting shutdown via signals'));
    }
}
