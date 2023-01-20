<?php

declare(strict_types = 1);

namespace Nayleen\Async\Bus\Event;

use Amp\PHPUnit\AsyncTestCase;
use Nayleen\Async\Bus\Message;
use Psr\Log\LoggerInterface;
use Psr\Log\LogLevel;

/**
 * @internal
 */
class EventBusMiddlewareTest extends AsyncTestCase
{
    /**
     * @test
     */
    public function passes_to_found_event_handlers(): void
    {
        $level = LogLevel::DEBUG;

        $message = $this->createMock(Message::class);
        $message->method('name')->willReturn('message');

        $logger = $this->createMock(LoggerInterface::class);
        $logger
            ->expects(self::exactly(6))
            ->method('log')
            ->withConsecutive(
                [$level, 'Started notifying event handlers', ['event' => $message]],
                [$level, 'Processing...'],
                [$level, 'Processing...'],
                [$level, 'Processing...'],
                [$level, 'Finished notifying event handlers', ['event' => $message]],
                [$level, 'Executing next handler...'],
            );

        $handler = static fn (Message $message) => $logger->log($level, 'Processing...');

        $handlers = new Handlers(
            [
                'message' => [$handler, $handler, $handler],
            ],
        );

        $middleware = new EventBusMiddleware($handlers, $logger, $level);
        $middleware->handle($message, fn (Message $message) => $logger->log($level, 'Executing next handler...'));
    }

    /**
     * @test
     */
    public function passes_to_next_handler_when_no_handlers_found(): void
    {
        $logger = $this->createMock(LoggerInterface::class);
        $logger->expects(self::once())->method('log');

        $middleware = new EventBusMiddleware(new Handlers(), $logger);
        $middleware->handle(
            $this->createMock(Message::class),
            fn () => $logger->log(LogLevel::DEBUG, 'Executing next handler...'),
        );
    }
}
