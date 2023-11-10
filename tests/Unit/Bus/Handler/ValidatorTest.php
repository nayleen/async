<?php

declare(strict_types = 1);

namespace Nayleen\Async\Bus\Handler;

use Amp\PHPUnit\AsyncTestCase;
use Closure;
use Nayleen\Async\Bus\Message;

/**
 * @internal
 */
final class ValidatorTest extends AsyncTestCase
{
    /**
     * @return array<string, array{handler: callable, result: bool}>
     */
    public function provideHandlers(): array
    {
        return [
            'handler instance' => [
                'handler' => new class() implements Handler {
                    public function __invoke(Message $message): void {}
                },
                'result' => true,
            ],

            'no parameters' => [
                'handler' => fn () => null,
                'result' => false,
            ],

            'first parameter missing type' => [
                'handler' => fn ($message) => null,
                'result' => false,
            ],

            'first parameter not a message' => [
                'handler' => fn (object $message) => null,
                'result' => false,
            ],

            'no return type' => [
                'handler' => fn (Message $message) => null,
                'result' => true,
            ],

            'correctly typed' => [
                'handler' => fn (Message $message) => null,
                'result' => true,
            ],

            'additional parameters' => [
                'handler' => fn (Message $message, bool $isValidated) => null,
                'result' => false,
            ],
        ];
    }

    /**
     * @test
     * @dataProvider provideHandlers
     */
    public function validates_handler_callbacks_correctly(Closure|Handler $handler, bool $expectedResult): void
    {
        self::assertSame($expectedResult, Validator::validate($handler));
    }
}
