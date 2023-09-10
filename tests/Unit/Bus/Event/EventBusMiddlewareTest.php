<?php

declare(strict_types = 1);

namespace Nayleen\Async\Bus\Event;

use Amp\PHPUnit\AsyncTestCase;
use Monolog\Handler\TestHandler;
use Monolog\Level;
use Monolog\Logger;
use Nayleen\Async\Bus\Message;
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
        $logger->pushHandler($logHandler = new TestHandler());
        $handler = static function (Message $message) use ($levelName, $logger): void {
            static $i = 0;
            $logger->log($levelName, 'Process #' . ++$i . '...');
        };

        $handlers = new EventHandlers(
            [
                'message' => [$handler, $handler, $handler],
            ],
        );

        $middleware = new EventBusMiddleware($handlers, $logger, $levelName);
        $middleware->handle($message, fn (Message $message) => $logger->log($levelName, 'Executing next handler...'));

        self::assertTrue(
            $logHandler->hasRecord(['message' => 'Started notifying event handlers', 'event' => $message], $level),
        );
        self::assertTrue($logHandler->hasRecord('Process #1...', $level));
        self::assertTrue($logHandler->hasRecord('Process #2...', $level));
        self::assertTrue($logHandler->hasRecord('Process #3...', $level));
        self::assertTrue(
            $logHandler->hasRecord(['message' => 'Finished notifying event handlers', 'event' => $message], $level),
        );
        self::assertTrue($logHandler->hasRecord('Executing next handler...', $level));
    }

    /**
     * @test
     */
    public function passes_to_next_handler_when_no_handlers_found(): void
    {
        $logger = $this->createMock(Logger::class);
        $logger->expects(self::once())->method('log');

        $middleware = new EventBusMiddleware(new EventHandlers(), $logger);
        $middleware->handle(
            $this->createMock(Message::class),
            fn () => $logger->log(LogLevel::DEBUG, 'Executing next handler...'),
        );
    }
}
