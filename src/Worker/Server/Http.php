<?php

declare(strict_types = 1);

namespace Nayleen\Async\Worker\Server;

use Amp\Http\Server\HttpServer;
use Nayleen\Async\Worker\Worker;
use Revolt\EventLoop\Driver;

final class Http extends Worker
{
    public function __construct(private readonly HttpServer $server)
    {
    }

    public function setup(Driver $loop): void
    {
        parent::setup($loop);

        $this->onSignals(
            $this->signals(),
            fn () => $this->server->stop(),
        );
    }

    public function run(): void
    {
        $this->server->start();
    }
}
