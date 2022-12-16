<?php

declare(strict_types = 1);

namespace Nayleen\Async\Bus\Command;

use Amp\PHPUnit\AsyncTestCase;
use Amp\Promise;
use Amp\Success;
use Generator;
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
    public function passes_to_found_handler(): Generator
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
                'message' => static function (Message $message) use ($logger, $level): Promise {
                    $logger->log($level, 'Processing...');

                    return new Success();
                },
            ],
        );

        $middleware = new CommandBusMiddleware($handlers, $logger, $level);
        yield $middleware->handle($message, function (Message $message) use ($logger, $level) {
            $logger->log($level, 'Executing next handler...');

            return new Success();
        });
    }

    /**
     * @test
     */
    public function throws_when_no_handler_is_found(): Generator
    {
        $this->expectException(OutOfBoundsException::class);

        $message = $this->createMock(Message::class);
        $message->method('name')->willReturn('message');

        $middleware = new CommandBusMiddleware(new Handlers());
        yield $middleware->handle($message, fn () => new Success());
    }
}
