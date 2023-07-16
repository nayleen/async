<?php

declare(strict_types = 1);

namespace Nayleen\Async\Task;

use Amp\ByteStream\WritableStream;
use Amp\Cancellation;
use Amp\Parallel\Context\Context;
use Amp\Parallel\Context\ContextFactory as ContextFactoryInterface;
use Amp\Parallel\Context\ProcessContext;

use function Amp\async;
use function Amp\ByteStream\pipe;

final class ContextFactory implements ContextFactoryInterface
{
    public function __construct(
        private readonly ContextFactoryInterface $contextFactory,
        private readonly WritableStream $stdOut,
        private readonly WritableStream $stdErr,
    ) {
    }

    public function start(array|string $script, ?Cancellation $cancellation = null): Context
    {
        $context = $this->contextFactory->start($script, $cancellation);

        if ($context instanceof ProcessContext) {
            async(pipe(...), $context->getStdout(), $this->stdOut, $cancellation)->ignore();
            async(pipe(...), $context->getStderr(), $this->stdErr, $cancellation)->ignore();
        }

        return $context;
    }
}
