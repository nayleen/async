<?php

declare(strict_types = 1);

namespace Nayleen\Async\Server;

use Amp\Http\Server\ErrorHandler;
use Amp\Http\Server\HttpServer;
use Amp\Http\Server\RequestHandler;
use Amp\PHPUnit\AsyncTestCase;
use Nayleen\Async\Test\TestKernel;

/**
 * @internal
 * @small
 */
final class HttpTest extends AsyncTestCase
{
    /**
     * @test
     */
    public function run_starts_the_server(): void
    {
        $requestHandler = self::createStub(RequestHandler::class);
        $errorHandler = self::createStub(ErrorHandler::class);

        $server = $this->createMock(HttpServer::class);
        $server->expects(self::once())->method('start')->with($requestHandler, $errorHandler);

        $httpServer = new Http($server, $requestHandler, $errorHandler, TestKernel::create());
        $httpServer->run();
    }
}
