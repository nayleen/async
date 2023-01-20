<?php

declare(strict_types = 1);

namespace Nayleen\Async;

use Amp\ByteStream\WritableResourceStream;
use Nayleen\Async\Console\StreamOutput;
use PHPUnit\Framework\TestCase;

/**
 * @internal
 */
class StreamOutputTest extends TestCase
{
    /**
     * @test
     */
    public function writes_lines_to_given_stream(): void
    {
        $stream = $this->createMock(WritableResourceStream::class);
        $stream->expects(self::once())->method('write')->with('test' . PHP_EOL);

        $output = new StreamOutput($stream);
        $output->writeln('test');
    }

    /**
     * @test
     */
    public function writes_to_given_stream(): void
    {
        $stream = $this->createMock(WritableResourceStream::class);
        $stream->expects(self::once())->method('write')->with('test');

        $output = new StreamOutput($stream);
        $output->write('test');
    }
}
