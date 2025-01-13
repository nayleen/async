<?php

declare(strict_types = 1);

namespace Nayleen\Async\Server;

use Amp\Http\Server\ErrorHandler;
use Amp\Http\Server\HttpServer;
use Amp\Http\Server\RequestHandler;
use Nayleen\Async\Kernel;
use Nayleen\Async\Worker;

readonly class Http extends Worker
{
    public function __construct(
        private HttpServer $server,
        private RequestHandler $requestHandler,
        private ErrorHandler $errorHandler,
        ?Kernel $kernel = null,
    ) {
        parent::__construct(
            $this->start(...),
            $kernel,
        );
    }

    private function start(): void
    {
        $this->server->start($this->requestHandler, $this->errorHandler);
    }
}
