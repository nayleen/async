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
 * @medium
 *
 * @covers \Nayleen\Async\Component\Bootstrapper
 * @covers \Nayleen\Async\Kernel
 */
final class KernelIntegrationTest extends AsyncTestCase
{
    /**
     * @test
     */
    public function can_trap_signals(): void
    {
        $logger = new Logger('test');
        $logger->pushHandler($log = new TestHandler());

        $cancellation = new DeferredCancellation();
        $components = [
            DependencyProvider::create([
                Logger::class => $logger,
            ]),
        ];

        $kernel = new Kernel($components, cancellation: $cancellation->getCancellation());
        $return = $kernel->run(function (Kernel $kernel) use ($cancellation): int {
            $kernel->loop()->defer(fn () => $cancellation->cancel());
            $kernel->trap();

            return 69;
        });

        self::assertNull($return);
        self::assertTrue($log->hasInfoThatContains('Awaiting shutdown via signals'));
    }
}
