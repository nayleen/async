<?php

declare(strict_types = 1);

namespace Nayleen\Async;

use Amp\Cluster\Cluster;

/**
 * @psalm-internal Nayleen\Async
 */
final readonly class Signals
{
    public function __construct(private Kernel $kernel) {}

    public function trap(int ...$signals): void
    {
        assert($this->kernel->io()->info('Awaiting shutdown via signals', ['trapped' => $signals]));

        try {
            Cluster::awaitTermination($this->kernel->cancellation);
        } finally {
            assert($this->kernel->io()->notice('Received shutdown request'));
        }
    }
}
