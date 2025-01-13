<?php

declare(strict_types = 1);

namespace Nayleen\Async\Console;

use Amp\ByteStream\ClosedException;
use Amp\ByteStream\WritableBuffer;
use Amp\ByteStream\WritableResourceStream;
use Amp\ByteStream\WritableStream;
use Amp\PHPUnit\AsyncTestCase;

use function Safe\putenv;

/**
 * @internal
 * @small
 *
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
            $output = new StreamOutput(self::createStub(WritableStream::class));
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
            $output = new StreamOutput(self::createStub(WritableStream::class));
        } finally {
            unset($_SERVER['NO_COLOR']);
        }

        self::assertFalse($output->isDecorated());
    }

    /**
     * @test
     */
    public function evaluates_color_support_when_writing_to_stream(): void
    {
        $resource = STDOUT;

        $stream = new WritableResourceStream($resource);
        $output = new StreamOutput($stream);

        self::assertSame(posix_isatty($resource), $output->isDecorated());
    }

    /**
     * @test
     */
    public function throws_when_writing_to_closed_stream(): void
    {
        $this->expectException(ClosedException::class);

        $stream = new WritableBuffer();
        $stream->close();

        $output = new StreamOutput($stream);
        $output->write('Hello World!');
    }

    /**
     * @test
     */
    public function writes_message_to_given_stream(): void
    {
        $stream = new WritableBuffer();
        $output = new StreamOutput($stream);

        $output->writeln('Hello World!');
        $stream->end();

        self::assertSame("Hello World!\n", $stream->buffer());
    }
}
