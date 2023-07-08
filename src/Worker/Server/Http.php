<?php

declare(strict_types = 1);

namespace Nayleen\Async\Worker\Server;

use Amp\Cancellation;
use Amp\Http\Server\DefaultErrorHandler;
use Amp\Http\Server\ErrorHandler;
use Amp\Http\Server\HttpServer;
use Amp\Http\Server\RequestHandler;
use Nayleen\Async\Timers;
use Nayleen\Async\Worker;

/**
 * @api
 */
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

    protected function execute(Cancellation $cancellation): void
    {
        $this->server->start($this->requestHandler, $this->errorHandler);
    }

    public function stop(): void
    {
        $this->server->stop();
        parent::stop();
    }
}
