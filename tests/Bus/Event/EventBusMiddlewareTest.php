<?php

declare(strict_types = 1);

namespace Nayleen\Async\Bus\Event;

use Amp\PHPUnit\AsyncTestCase;
use Monolog\Handler\TestHandler;
use Monolog\Level;
use Monolog\Logger;
use Nayleen\Async\Bus\Message;
use Psr\Log\LoggerInterface;
use Psr\Log\LogLevel;

/**
 * @internal
 */
final class EventBusMiddlewareTest extends AsyncTestCase
{
    /**
     * @test
     */
    public function passes_to_found_event_handlers(): void
    {
        $levelName = LogLevel::DEBUG;
        $level = Level::fromName($levelName);

        $message = $this->createMock(Message::class);
        $message->method('name')->willReturn('message');

        $logger = new Logger('test');
        $logger->pushHandler($testHandler = new TestHandler());
        $handler = static fn (Message $message) => $logger->log($levelName, 'Processing...');

        $handlers = new Handlers(
            [
                'message' => [$handler, $handler, $handler],
            ],
        );

        $middleware = new EventBusMiddleware($handlers, $logger, $levelName);
        $middleware->handle($message, fn (Message $message) => $logger->log($levelName, 'Executing next handler...'));

        self::assertTrue($testHandler->hasRecord(['message' => 'Started notifying event handlers', 'event' => $message], $level));
        self::assertTrue($testHandler->hasRecord('Processing...', $level));
        self::assertTrue($testHandler->hasRecord('Processing...', $level));
        self::assertTrue($testHandler->hasRecord('Processing...', $level));
        self::assertTrue($testHandler->hasRecord(['message' => 'Finished notifying event handlers', 'event' => $message], $level));
        self::assertTrue($testHandler->hasRecord('Executing next handler...', $level));
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
