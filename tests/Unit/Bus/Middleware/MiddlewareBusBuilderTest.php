<?php

declare(strict_types = 1);

namespace Nayleen\Async\Bus\Middleware;

use Amp\PHPUnit\AsyncTestCase;
use Monolog\Handler\TestHandler;
use Monolog\Level;
use Monolog\Logger;
use Nayleen\Async\Bus\Message;
use OutOfBoundsException;

/**
 * @internal
 */
final class MiddlewareBusBuilderTest extends AsyncTestCase
{
    /**
     * @test
     */
    public function can_create_command_busses(): void
    {
        $logger = new Logger('test');
        $logger->pushHandler($testHandler = new TestHandler());

        $logLevel = Level::Debug;

        $builder = new MiddlewareBusBuilder(logger: $logger, level: $logLevel);
        $bus = $builder->command();

        $this->expectException(OutOfBoundsException::class);
        $bus->handle($this->createStub(Message::class));

        self::assertTrue($testHandler->hasRecord('Started handling message', $logLevel));
        self::assertTrue($testHandler->hasRecord('Finished handling message', $logLevel));
    }

    /**
     * @test
     */
    public function can_create_event_busses(): void
    {
        $logger = new Logger('test');
        $logger->pushHandler($testHandler = new TestHandler());

        $logLevel = Level::Debug;

        $builder = new MiddlewareBusBuilder(logger: $logger, level: $logLevel);
        $bus = $builder->event();

        $bus->handle($this->createStub(Message::class));

        self::assertTrue($testHandler->hasRecord('Started handling message', $logLevel));
        self::assertTrue($testHandler->hasRecord('Finished handling message', $logLevel));
    }
}
