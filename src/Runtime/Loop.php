<?php

declare(strict_types = 1);

namespace Nayleen\Async\Runtime;

use Amp\Loop\Driver;
use Throwable;

final class Loop implements Runtime
{
    public function __construct(private readonly Driver $loop)
    {
    }

    public function run(?callable $deferred = null): int
    {
        $exitHandler = fn () => $this->loop->stop();

        $this->loop->onSignal(SIGINT, $exitHandler);
        $this->loop->onSignal(SIGTERM, $exitHandler);

        if ($deferred) {
            $this->loop->defer($deferred);
        }

        try {
            $this->loop->run();
        } catch (Throwable) {
            return 255;
        }

        return 0;
    }
}
