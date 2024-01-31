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
use Nayleen\Async\Components;
use Nayleen\Async\Kernel;
use Nayleen\Finder\Engine\MemoizedEngine;
use Psr\Log\LoggerInterface;
use Revolt\EventLoop;

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

    public static function create(
        ?EventLoop\Driver $loop = null,
        Cancellation $cancellation = new NullCancellation(),
        ByteStream\WritableStream $stdOut = new ByteStream\WritableBuffer(),
        ByteStream\WritableStream $stdErr = new ByteStream\WritableBuffer(),
        ByteStream\ReadableStream $stdIn = new ByteStream\ReadableBuffer(),
    ): self {
        $components = [
            ...self::finder(),
            DependencyProvider::create([
                EventLoop\Driver::class => $loop ?? EventLoop::getDriver(),
            ]),
        ];

        return new self($components, $cancellation, $stdOut, $stdErr, $stdIn);
    }

    public static function finder(): Finder
    {
        static $finder = null;

        return $finder ??= new Finder(new MemoizedEngine(defaultEngine()));
    }

    public function trap(int ...$signals): void {}

    public function withDependency(string $name, mixed $value): self
    {
        return new self(
            new Components(
                [
                    ...$this->components,
                    DependencyProvider::create([$name => $value]),
                ],
            ),
            $this->cancellation,
        );
    }
}
