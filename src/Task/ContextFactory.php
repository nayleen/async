<?php

declare(strict_types = 1);

namespace Nayleen\Async\Task;

use Amp\ByteStream;
use Amp\Cancellation;
use Amp\Parallel\Context\Context;
use Amp\Parallel\Context\ContextFactory as ContextFactoryInterface;
use Amp\Parallel\Context\ProcessContext;
use Amp\Parallel\Context\ProcessContextFactory;
use Amp\Parallel\Context\ThreadContext;
use Amp\Parallel\Context\ThreadContextFactory;
use Amp\Parallel\Ipc\IpcHub;

use function Amp\async;

final class ContextFactory implements ContextFactoryInterface
{
    private readonly ContextFactoryInterface $contextFactory;

    public function __construct(
        private readonly ByteStream\WritableStream $stdOut,
        private readonly ByteStream\WritableStream $stdErr,
        IpcHub $ipcHub,
    ) {
        if (ThreadContext::isSupported()) {
            $this->contextFactory = new ThreadContextFactory(ipcHub: $ipcHub);
        } else {
            $this->contextFactory = new ProcessContextFactory(ipcHub: $ipcHub);
        }
    }

    public function start(array|string $script, ?Cancellation $cancellation = null): Context
    {
        $context = $this->contextFactory->start($script, $cancellation);

        if ($context instanceof ProcessContext) {
            async(ByteStream\pipe(...), $context->getStdout(), $this->stdOut, $cancellation)->ignore();
            async(ByteStream\pipe(...), $context->getStderr(), $this->stdErr, $cancellation)->ignore();
        }

        return $context;
    }
}
