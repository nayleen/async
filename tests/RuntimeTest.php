<?php

declare(strict_types = 1);

namespace Nayleen\Async;

use Nayleen\Async\Component\DependencyProvider;
use PHPUnit\Framework\TestCase;
use Psr\Log\LoggerInterface;
use Psr\Log\NullLogger;
use Revolt\EventLoop;

/**
 * @internal
 */
final class RuntimeTest extends TestCase
{
    private function createComponents(
        ?EventLoop\Driver $loop = null,
        ?LoggerInterface $stdErrLogger = null,
        ?LoggerInterface $stdOutLogger = null,
        Component ...$components,
    ): Components {
        return new Components(
            [
                DependencyProvider::create([
                    EventLoop\Driver::class => $loop ?? EventLoop::getDriver(),
                    'async.logger.stderr' => $stdErrLogger ?? new NullLogger(),
                    'async.logger.stdout' => $stdOutLogger ?? new NullLogger(),
                ]),
                ...$components,
            ],
        );
    }

    /**
     * @test
     */
    public function executes_in_kernel_context(): void
    {
        stream_wrapper_unregister('file');
        stream_wrapper_restore('file');

        $kernel = new Kernel($this->createComponents());
        $runtime = new TestRuntime();

        self::assertSame(420, $kernel->execute($runtime));
    }
}

/**
 * @internal
 */
final class TestRuntime extends Runtime
{
    protected function execute(Kernel $kernel): int
    {
        return 420;
    }
}
