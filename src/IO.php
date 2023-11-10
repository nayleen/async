<?php

declare(strict_types = 1);

namespace Nayleen\Async;

use Amp\ByteStream\ReadableStream;
use Amp\ByteStream\WritableStream;
use Amp\Cancellation;
use Amp\NullCancellation;
use Monolog\Level;
use Monolog\Logger;
use Stringable;

/**
 * @method bool alert(string|Stringable $message, array $context = [])
 * @method bool critical(string|Stringable $message, array $context = [])
 * @method bool debug(string|Stringable $message, array $context = [])
 * @method bool emergency(string|Stringable $message, array $context = [])
 * @method bool error(string|Stringable $message, array $context = [])
 * @method bool info(string|Stringable $message, array $context = [])
 * @method bool notice(string|Stringable $message, array $context = [])
 * @method bool warning(string|Stringable $message, array $context = [])
 */
class IO
{
    public function __construct(
        public readonly ReadableStream $input,
        public readonly WritableStream $output,
        public readonly Logger $logger,
    ) {}

    private function normalize(int|Level|string $level): Level
    {
        if ($level instanceof Level) {
            return $level;
        }

        if (is_int($level)) {
            return Level::from($level);
        }

        $level = strtoupper($level);
        assert(in_array($level, Level::NAMES, true));

        return Level::fromName($level);
    }

    /**
     * @param non-empty-string|Stringable $message
     * @param mixed[] $context
     */
    public function log(int|Level|string $level, string|Stringable $message, array $context = []): bool
    {
        $message = (string) $message;

        assert($message !== '');
        $this->logger->log($this->normalize($level), $message, $context);

        return true;
    }

    public function read(Cancellation $cancellation = new NullCancellation()): ?string
    {
        return $this->input->read($cancellation);
    }

    /**
     * @param non-empty-string $bytes
     */
    public function write(string $bytes): bool
    {
        assert($bytes !== '');
        $this->output->write($bytes);

        return true;
    }

    /**
     * @param non-empty-string $method
     * @param array{0: non-empty-string|Stringable, 1: mixed[]} $arguments
     */
    public function __call(string $method, array $arguments): bool
    {
        $this->log($method, ...$arguments);

        return true;
    }
}
