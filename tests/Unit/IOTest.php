<?php

declare(strict_types = 1);

namespace Nayleen\Async;

use Amp\ByteStream\ReadableStream;
use Amp\ByteStream\WritableStream;
use Amp\PHPUnit\AsyncTestCase;
use Monolog\Logger;

/**
 * @internal
 */
final class IOTest extends AsyncTestCase
{
    /**
     * @test
     */
    public function can_read_from_stdin(): void
    {
        $input = $this->createMock(ReadableStream::class);
        $input->expects(self::once())->method('read')->willReturn('test');

        $io = new IO($input, $this->createStub(WritableStream::class), $this->createStub(Logger::class));
        self::assertSame('test', $io->read());
    }

    /**
     * @test
     */
    public function can_write_to_stdout(): void
    {
        $output = $this->createMock(WritableStream::class);
        $output->expects(self::once())->method('write')->with('test');

        $io = new IO($this->createStub(ReadableStream::class), $output, $this->createStub(Logger::class));
        $io->write('test');
    }
}
