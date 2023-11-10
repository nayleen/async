<?php

declare(strict_types = 1);

namespace Nayleen\Async;

use Amp\ByteStream\ReadableStream;
use Amp\ByteStream\WritableStream;
use Amp\PHPUnit\AsyncTestCase;
use Generator;
use Monolog\Level;
use Monolog\Logger;

/**
 * @internal
 */
final class IOTest extends AsyncTestCase
{
    public static function logLevels(): Generator
    {
        $names = Level::NAMES;

        foreach ($names as $level) {
            yield "string '{$level}'" => [$level];
        }

        foreach (Level::VALUES as $level) {
            yield "psr integer '{$level}'" => [$level];
        }

        foreach (array_map(static fn (string $level) => Level::fromName($level), $names) as $level) {
            yield "enum '" . $level->getName() . "'" => [$level];
        }
    }

    /**
     * @test
     * @dataProvider logLevels
     */
    public function accepts_all_variants_of_log_level(int|Level|string $level): void
    {
        $logger = $this->createMock(Logger::class);
        $logger->expects(self::once())->method('log')->with(self::callback(fn (Level $level): bool => true), 'test');

        $io = new IO($this->createStub(ReadableStream::class), $this->createStub(WritableStream::class), $logger);
        $io->log($level, 'test');
    }

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
