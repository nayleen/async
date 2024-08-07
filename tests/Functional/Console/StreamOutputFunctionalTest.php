<?php

declare(strict_types = 1);

namespace Nayleen\Async\Console;

use Amp\ByteStream\ClosedException;
use Amp\ByteStream\WritableBuffer;
use Amp\ByteStream\WritableResourceStream;
use Amp\PHPUnit\AsyncTestCase;

/**
 * @internal
 */
final class StreamOutputFunctionalTest extends AsyncTestCase
{
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
