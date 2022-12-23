<?php

declare(strict_types = 1);

namespace Nayleen\Async\Bus\Command;

use Amp\PHPUnit\AsyncTestCase;
use Nayleen\Async\Bus\Message;
use OutOfBoundsException;
use Psr\Log\LoggerInterface;
use Psr\Log\LogLevel;

/**
 * @internal
 */
class CommandBusMiddlewareTest extends AsyncTestCase
{
    /**
     * @test
     */
    public function passes_to_found_handler(): void
    {
        $level = LogLevel::DEBUG;

        $message = $this->createMock(Message::class);
        $message->method('name')->willReturn('message');

        $logger = $this->createMock(LoggerInterface::class);
        $logger
            ->expects(self::exactly(4))
            ->method('log')
            ->withConsecutive(
                [$level, 'Started executing command handler', ['command' => $message]],
                [$level, 'Processing...'],
                [$level, 'Finished executing command handler', ['command' => $message]],
                [$level, 'Executing next handler...'],
            );

        $handlers = new Handlers(
            [
                'message' => static function (Message $message) use ($logger, $level): void {
                    $logger->log($level, 'Processing...');
                },
            ],
        );

        $middleware = new CommandBusMiddleware($handlers, $logger, $level);
        $middleware->handle($message, function (Message $message) use ($logger, $level): void {
            $logger->log($level, 'Executing next handler...');
        });
    }

    /**
     * @test
     */
    public function throws_when_no_handler_is_found(): void
    {
        $this->expectException(OutOfBoundsException::class);

        $message = $this->createMock(Message::class);
        $message->method('name')->willReturn('message');

        $middleware = new CommandBusMiddleware(new Handlers());
        $middleware->handle($message, fn () => null);
    }
}
