<?php

declare(strict_types = 1);

namespace Nayleen\Async;

use Amp\ByteStream\ReadableStream;
use Amp\ByteStream\WritableStream;
use Amp\Cancellation;
use Amp\NullCancellation;
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
        /** @phpstan-ignore-next-line */
        $this->logger->log($method, ...$arguments);

        return true;
    }
}
