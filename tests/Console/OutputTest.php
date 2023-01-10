<?php

declare(strict_types = 1);

namespace Nayleen\Async\Console;

use Amp\ByteStream\WritableResourceStream;
use PHPUnit\Framework\TestCase;

class OutputTest extends TestCase
{
    /**
     * @test
     */
    public function writes_to_given_stream(): void
    {
        $stream = $this->createMock(WritableResourceStream::class);
        $stream->expects(self::once())->method('write')->with('test');

        $output = new Output($stream);
        $output->write('test');
    }

    /**
     * @test
     */
    public function writes_lines_to_given_stream(): void
    {
        $stream = $this->createMock(WritableResourceStream::class);
        $stream->expects(self::once())->method('write')->with('test' . PHP_EOL);

        $output = new Output($stream);
        $output->writeln('test');
    }
}
