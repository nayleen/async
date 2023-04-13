<?php

declare(strict_types = 1);

namespace Nayleen\Async\Worker\Server;

use Amp\Http\Server\ErrorHandler;
use Amp\Http\Server\HttpServer;
use Amp\Http\Server\RequestHandler;
use Nayleen\Async\Kernel;
use PHPUnit\Framework\TestCase;

/**
 * @internal
 */
class HttpTest extends TestCase
{
    /**
     * @test
     */
    public function can_run(): void
    {
        $errorHandler = $this->createStub(ErrorHandler::class);
        $kernel = $this->createStub(Kernel::class);
        $requestHandler = $this->createStub(RequestHandler::class);

        $server = $this->createMock(HttpServer::class);
        $server->expects(self::once())->method('start')->with($requestHandler, $errorHandler);

        $worker = new Http($server, $requestHandler, $errorHandler);
        $worker->run($kernel);
    }

    /**
     * @test
     */
    public function stopping_worker_stops_http_server(): void
    {
        $errorHandler = $this->createStub(ErrorHandler::class);
        $requestHandler = $this->createStub(RequestHandler::class);

        $server = $this->createMock(HttpServer::class);
        $server->expects(self::once())->method('stop');

        $worker = new Http($server, $requestHandler, $errorHandler);
        $worker->stop();
    }
}
