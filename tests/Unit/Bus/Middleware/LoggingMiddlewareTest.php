<?php

declare(strict_types = 1);

namespace Nayleen\Async\Bus\Middleware;

use Amp\PHPUnit\AsyncTestCase;
use Monolog\Handler\TestHandler;
use Monolog\Level;
use Monolog\Logger;
use Nayleen\Async\Bus\Message;
use Psr\Log\LogLevel;

/**
 * @internal
 */
final class LoggingMiddlewareTest extends AsyncTestCase
{
    /**
     * @test
     */
    public function invokes_logger(): void
    {
        $levelName = LogLevel::DEBUG;
        $level = Level::fromName($levelName);

        $message = $this->createMock(Message::class);

        $logger = new Logger('test');
        $logger->pushHandler($handler = new TestHandler());

        $middleware = new LoggingMiddleware($logger, $levelName);
        $middleware->handle(
            $message,
            $this->createCallback(
                1,
                fn (Message $message) => $logger->log($level, 'Processing...', ['message' => $message]),
            ),
        );

        self::assertTrue(
            $handler->hasRecord(['message' => 'Started handling message', 'processed_message' => $message], $level),
        );
        self::assertTrue($handler->hasRecord(['message' => 'Processing...', 'processed_message' => $message], $level));
        self::assertTrue(
            $handler->hasRecord(['message' => 'Finished handling message', 'processed_message' => $message], $level),
        );
    }
}
