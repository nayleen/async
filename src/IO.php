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
 * @method true alert(string|Stringable $message, array $context = [])
 * @method true critical(string|Stringable $message, array $context = [])
 * @method true debug(string|Stringable $message, array $context = [])
 * @method true emergency(string|Stringable $message, array $context = [])
 * @method true error(string|Stringable $message, array $context = [])
 * @method true info(string|Stringable $message, array $context = [])
 * @method true notice(string|Stringable $message, array $context = [])
 * @method true warning(string|Stringable $message, array $context = [])
 */
readonly class IO
{
    public function __construct(
        public ReadableStream $input,
        public WritableStream $output,
        public Logger $logger,
    ) {}

    public function read(Cancellation $cancellation = new NullCancellation()): ?string
    {
        return $this->input->read($cancellation);
    }

    /**
     * @param non-empty-string $bytes
     */
    public function write(string $bytes): true
    {
        assert($bytes !== '');
        $this->output->write($bytes);

        return true;
    }

    /**
     * @param non-empty-string $method
     * @param array{0: non-empty-string|Stringable, 1: mixed[]} $arguments
     */
    public function __call(string $method, array $arguments): true
    {
        /** @phpstan-ignore-next-line */
        $this->logger->log($method, ...$arguments);

        return true;
    }
}
