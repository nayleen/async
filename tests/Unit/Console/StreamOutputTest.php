<?php

declare(strict_types = 1);

namespace Nayleen\Async\Console;

use Amp\ByteStream\WritableStream;
use Amp\PHPUnit\AsyncTestCase;

use function Safe\putenv;

/**
 * @internal
 * @backupGlobals true
 */
final class StreamOutputTest extends AsyncTestCase
{
    /**
     * @test
     */
    public function can_suppress_color_support_via_env_variable(): void
    {
        try {
            putenv('NO_COLOR=1');
            $output = new StreamOutput($this->createStub(WritableStream::class));
        } finally {
            putenv('NO_COLOR');
        }

        self::assertFalse($output->isDecorated());
    }

    /**
     * @test
     */
    public function can_suppress_color_support_via_server_variable(): void
    {
        try {
            $_SERVER['NO_COLOR'] = 1;
            $output = new StreamOutput($this->createStub(WritableStream::class));
        } finally {
            unset($_SERVER['NO_COLOR']);
        }

        self::assertFalse($output->isDecorated());
    }
}
