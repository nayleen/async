<?php

declare(strict_types = 1);

namespace Nayleen\Async\Server;

use Amp\Http\Server\ErrorHandler;
use Amp\Http\Server\HttpServer;
use Amp\Http\Server\RequestHandler;
use Amp\PHPUnit\AsyncTestCase;
use Nayleen\Async\Test\TestKernel;
use Revolt\EventLoop;

/**
 * @internal
 */
final class HttpTest extends AsyncTestCase
{
    /**
     * @test
     */
    public function can_run(): void
    {
        $errorHandler = $this->createStub(ErrorHandler::class);
        $requestHandler = $this->createStub(RequestHandler::class);

        $server = $this->createMock(HttpServer::class);
        $server->expects(self::once())->method('start')->with($requestHandler, $errorHandler);
        $server->expects(self::once())->method('stop');

        $worker = new Http($server, $requestHandler, $errorHandler);
        $worker->execute(TestKernel::create($this->createStub(EventLoop\Driver::class)));
    }
}
