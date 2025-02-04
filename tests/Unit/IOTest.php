<?php

declare(strict_types = 1);

namespace Nayleen\Async;

use Amp\ByteStream\ReadableStream;
use Amp\ByteStream\WritableStream;
use Amp\PHPUnit\AsyncTestCase;
use Monolog\Logger;
use Psr\Log\LogLevel;
use ReflectionClass;

/**
 * @internal
 * @small
 */
final class IOTest extends AsyncTestCase
{
    /**
     * @return iterable<non-empty-string, array{0: non-empty-string}>
     */
    public static function provideLogLevels(): iterable
    {
        $logLevels = (new ReflectionClass(LogLevel::class))->getConstants();
        sort($logLevels, SORT_NATURAL);

        foreach ($logLevels as $logLevel) {
            assert(is_string($logLevel) && $logLevel !== '');
            yield $logLevel => [$logLevel];
        }
    }

    /**
     * @test
     */
    public function can_read_from_stdin(): void
    {
        $input = $this->createMock(ReadableStream::class);
        $input->expects(self::once())->method('read')->willReturn('test');

        $io = new IO($input, self::createStub(WritableStream::class), self::createStub(Logger::class));
        self::assertSame('test', $io->read());
    }

    /**
     * @test
     */
    public function can_write_to_stdout(): void
    {
        $output = $this->createMock(WritableStream::class);
        $output->expects(self::once())->method('write')->with('test');

        $io = new IO(self::createStub(ReadableStream::class), $output, self::createStub(Logger::class));
        $io->write('test');
    }

    /**
     * @test
     * @dataProvider provideLogLevels
     */
    public function proxies_log_level_calls_to_logger(string $level): void
    {
        $logger = $this->createMock(Logger::class);
        $logger->expects(self::once())->method('log')->with($level, 'Message');

        $io = new IO(self::createStub(ReadableStream::class), self::createStub(WritableStream::class), $logger);
        $io->{$level}('Message'); // @phpstan-ignore-line
    }
}
