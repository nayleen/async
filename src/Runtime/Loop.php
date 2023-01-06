<?php

declare(strict_types = 1);

namespace Nayleen\Async\Runtime;

use Nayleen\Async\Kernel\Kernel;
use Revolt\EventLoop\Driver;
use Throwable;

final class Loop extends Runtime
{
    /**
     * @var callable|null
     */
    private $callback = null;

    public function __construct(
        Kernel $kernel,
        private readonly Driver $loop,
    ) {
        parent::__construct($kernel);
    }

    public function defer(?callable $callback): self
    {
        $this->callback = $callback;

        return $this;
    }

    protected function execute(): ?int
    {
        // TODO: Implement execute() method.
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
