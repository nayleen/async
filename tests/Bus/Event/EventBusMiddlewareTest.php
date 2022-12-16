<?php

declare(strict_types = 1);

namespace Nayleen\Async\Bus\Event;

use Amp\PHPUnit\AsyncTestCase;
use Amp\Promise;
use Amp\Success;
use Generator;
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
    public function passes_to_found_event_handlers(): Generator
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

        $handler = static function (Message $message) use ($logger, $level): Promise {
            $logger->log($level, 'Processing...');

            return new Success();
        };

        $handlers = new Handlers(
            [
                'message' => [$handler, $handler, $handler],
            ],
        );

        $middleware = new EventBusMiddleware($handlers, $logger, $level);
        yield $middleware->handle($message, function (Message $message) use ($logger, $level) {
            $logger->log($level, 'Executing next handler...');

            return new Success();
        });
    }

    /**
     * @test
     */
    public function passes_to_next_handler_when_no_handlers_found(): Generator
    {
        $logger = $this->createMock(LoggerInterface::class);
        $logger->expects(self::once())->method('log');

        $middleware = new EventBusMiddleware(new Handlers(), $logger);
        yield $middleware->handle($this->createMock(Message::class), function () use ($logger) {
            $logger->log(LogLevel::DEBUG, 'Executing next handler...');

            return new Success();
        });
    }
}
