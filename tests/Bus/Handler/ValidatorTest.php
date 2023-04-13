<?php

declare(strict_types = 1);

namespace Nayleen\Async\Bus\Handler;

use Nayleen\Async\Bus\Message;
use PHPUnit\Framework\TestCase;

/**
 * @internal
 */
class ValidatorTest extends TestCase
{
    /**
     * @return array<string, array{handler: callable, result: bool}>
     */
    public function provideHandlers(): array
    {
        return [
            'handler instance' => [
                'handler' => $this->createMock(Handler::class),
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
    public function validates_handler_callbacks_correctly(callable $handler, bool $expectedResult): void
    {
        $validator = new class() {
            use Validator {
                validateHandler as public;
            }
        };

        self::assertSame($expectedResult, $validator->validateHandler($handler));
    }
}
