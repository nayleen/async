<?php

declare(strict_types = 1);

namespace Nayleen\Async\Server;

use Amp\Http\Server\DefaultErrorHandler;
use Amp\Http\Server\ErrorHandler;
use Amp\Http\Server\HttpServer;
use Amp\Http\Server\RequestHandler;
use Nayleen\Async\Kernel;
use Nayleen\Async\Task\Worker;
use Nayleen\Async\Timers;

class Http extends Worker
{
    public function __construct(
        private readonly HttpServer $server,
        private readonly RequestHandler $requestHandler,
        private readonly ErrorHandler $errorHandler = new DefaultErrorHandler(),
        Timers $timers = new Timers(),
    ) {
        parent::__construct($timers);
    }

    protected function execute(Kernel $kernel): null
    {
        try {
            $this->server->start($this->requestHandler, $this->errorHandler);

            return parent::execute($kernel);
        } finally {
            $this->server->stop();
        }
    }
}
