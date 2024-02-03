<?php

declare(strict_types = 1);

namespace Nayleen\Async\Task;

use Amp\ByteStream;
use Amp\Cancellation;
use Amp\Parallel\Context\Context;
use Amp\Parallel\Context\ContextFactory as ContextFactoryInterface;
use Amp\Parallel\Context\ProcessContext;

use function Amp\async;

readonly class ContextFactory implements ContextFactoryInterface
{
    public function __construct(
        private ByteStream\WritableStream $stdOut,
        private ByteStream\WritableStream $stdErr,
        private ContextFactoryInterface $contextFactory,
    ) {}

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
