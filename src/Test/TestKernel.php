<?php

declare(strict_types = 1);

namespace Nayleen\Async\Test;

use Amp\ByteStream;
use Amp\Cancellation;
use Amp\NullCancellation;
use Amp\Sync\Channel;
use Monolog\Handler\TestHandler;
use Monolog\Logger;
use Nayleen\Async\Component\DependencyProvider;
use Nayleen\Async\Component\Finder;
use Nayleen\Async\Kernel;
use Nayleen\Finder\Engine\MemoizedEngine;
use Psr\Log\LoggerInterface;

use function Nayleen\Finder\defaultEngine;

final readonly class TestKernel extends Kernel
{
    public TestHandler $log;

    public function __construct(
        ?iterable $components = null,
        Cancellation $cancellation = new NullCancellation(),
        ByteStream\WritableStream $stdOut = new ByteStream\WritableBuffer(),
        ByteStream\WritableStream $stdErr = new ByteStream\WritableBuffer(),
        ByteStream\ReadableStream $stdIn = new ByteStream\ReadableBuffer(),
    ) {
        $logger = new Logger('TestKernel');
        $logger->pushHandler($this->log = new TestHandler(bubble: false));

        $components = [
            ...($components ?? self::finder()),
            DependencyProvider::create([
                Channel::class => new ByteStream\StreamChannel($stdIn, $stdOut),
                Logger::class => $logger,
                LoggerInterface::class => $logger,
                'async.stderr' => $stdErr,
                'async.stdin' => $stdIn,
                'async.stdout' => $stdOut,
            ]),
        ];

        parent::__construct($components, null, $cancellation);
    }

    /**
     * @param array<class-string|string, object> $dependencies
     */
    public static function create(
        array $dependencies,
        Cancellation $cancellation = new NullCancellation(),
        ByteStream\WritableStream $stdOut = new ByteStream\WritableBuffer(),
        ByteStream\WritableStream $stdErr = new ByteStream\WritableBuffer(),
        ByteStream\ReadableStream $stdIn = new ByteStream\ReadableBuffer(),
    ): self {
        $components = [
            ...self::finder(),
            DependencyProvider::create($dependencies),
        ];

        return new self($components, $cancellation, $stdOut, $stdErr, $stdIn);
    }

    public static function finder(): Finder
    {
        static $finder = null;

        return $finder ??= new Finder(new MemoizedEngine(defaultEngine()));
    }

    public function trap(Cancellation $cancellation = new NullCancellation()): void {}
}
