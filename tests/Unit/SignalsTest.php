<?php

declare(strict_types = 1);

namespace Nayleen\Async;

use Amp\CancelledException;
use Amp\DeferredCancellation;
use Amp\PHPUnit\AsyncTestCase;
use Nayleen\Async\Test\TestKernel;

/**
 * @internal
 * @small
 *
 * @covers \Nayleen\Async\Signals
 */
final class SignalsTest extends AsyncTestCase
{
    /**
     * @test
     */
    public function trap_awaits_termination(): void
    {
        $this->setTimeout(1.0);
        $cancellation = new DeferredCancellation();

        $kernel = new TestKernel(cancellation: $cancellation->getCancellation());
        $kernel->loop()->defer(static fn () => $cancellation->cancel());

        try {
            (new Signals($kernel))->trap(SIGINT, SIGTERM);
        } catch (CancelledException) {
        }

        self::assertTrue($kernel->log->hasInfoThatContains('Awaiting shutdown via signals'));
        self::assertTrue($kernel->log->hasNoticeThatContains('Received shutdown request'));
    }
}
