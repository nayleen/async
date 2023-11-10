<?php

declare(strict_types = 1);

namespace Nayleen\Async\Bus\Command;

use Amp\PHPUnit\AsyncTestCase;
use Monolog\Handler\TestHandler;
use Monolog\Level;
use Monolog\Logger;
use Nayleen\Async\Bus\Message;
use OutOfBoundsException;
use Psr\Log\LogLevel;

/**
 * @internal
 */
final class CommandBusMiddlewareTest extends AsyncTestCase
{
    /**
     * @test
     */
    public function passes_to_found_handler(): void
    {
        $levelName = LogLevel::DEBUG;
        $level = Level::fromName($levelName);

        $message = $this->createMock(Message::class);
        $message->method('name')->willReturn('message');

        $logger = new Logger('test');
        $logger->pushHandler($handler = new TestHandler());

        $handlers = new CommandHandlers(
            [
                'message' => static function (Message $message) use ($logger, $level): void {
                    $logger->log($level, 'Processing...');
                },
            ],
        );

        $middleware = new CommandBusMiddleware($handlers, $logger, $levelName);
        $middleware->handle($message, function () use ($logger, $level): void {
            $logger->log($level, 'Executing next handler...');
        });

        self::assertTrue(
            $handler->hasRecord(['message' => 'Started executing command handler', 'command' => $message], $level),
        );
        self::assertTrue($handler->hasRecord('Processing...', $level));
        self::assertTrue(
            $handler->hasRecord(['message' => 'Finished executing command handler', 'command' => $message], $level),
        );
        self::assertTrue($handler->hasRecord('Executing next handler...', $level));
    }

    /**
     * @test
     */
    public function throws_when_no_handler_is_found(): void
    {
        $this->expectException(OutOfBoundsException::class);

        $message = $this->createMock(Message::class);
        $message->method('name')->willReturn('message');

        $middleware = new CommandBusMiddleware(new CommandHandlers());
        $middleware->handle($message, function (): void {});
    }
}
