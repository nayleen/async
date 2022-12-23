<?php

declare(strict_types = 1);

namespace Nayleen\Async\Bus\Middleware;

use Amp\PHPUnit\AsyncTestCase;
use Nayleen\Async\Bus\Message;
use Psr\Log\LoggerInterface;
use Psr\Log\LogLevel;

/**
 * @internal
 */
class LoggingMiddlewareTest extends AsyncTestCase
{
    /**
     * @test
     */
    public function invokes_logger(): void
    {
        $level = LogLevel::DEBUG;
        $message = $this->createMock(Message::class);

        $logger = $this->createMock(LoggerInterface::class);
        $logger
            ->expects(self::exactly(3))
            ->method('log')
            ->withConsecutive(
                [$level, 'Started handling message', ['message' => $message]],
                [$level, 'Processing...', ['message' => $message]],
                [$level, 'Finished handling message', ['message' => $message]],
            );

        $middleware = new LoggingMiddleware($logger, $level);
        $middleware->handle(
            $message,
            $this->createCallback(1, fn (Message $message) => $logger->log($level, 'Processing...', ['message' => $message])),
        );
    }
}
