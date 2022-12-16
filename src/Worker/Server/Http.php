<?php

declare(strict_types = 1);

namespace Nayleen\Async\Worker\Server;

use Amp\Http\Server\HttpServer;
use Amp\Loop\Driver;
use Amp\Promise;
use Nayleen\Async\Worker\Worker;

final class Http extends Worker
{
    private const DEFAULT_SHUTDOWN_TIMEOUT = HttpServer::DEFAULT_SHUTDOWN_TIMEOUT;

    public function __construct(
        private readonly HttpServer $server,
        private readonly int $shutdownTimeout = self::DEFAULT_SHUTDOWN_TIMEOUT,
    ) {
    }

    public function setup(Driver $loop): void
    {
        parent::setup($loop);

        $this->onSignals(
            $this->signals(),
            fn () => $this->server->stop($this->shutdownTimeout),
        );
    }

    public function run(): Promise
    {
        return $this->server->start();
    }
}
