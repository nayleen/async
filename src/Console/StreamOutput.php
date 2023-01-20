<?php

declare(strict_types = 1);

namespace Nayleen\Async\Console;

use Amp\ByteStream\WritableResourceStream;
use Symfony\Component\Console\Formatter\OutputFormatterInterface;
use Symfony\Component\Console\Output\Output as BaseOutput;
use Symfony\Component\Console\Output\OutputInterface;

final class Output extends BaseOutput
{
    public function __construct(
        private readonly WritableResourceStream $stream,
        ?int $verbosity = OutputInterface::VERBOSITY_NORMAL,
        bool $decorated = false,
        ?OutputFormatterInterface $formatter = null,
    ) {
        parent::__construct($verbosity, $decorated, $formatter);
    }

    protected function doWrite(string $message, bool $newline): void
    {
        if ($newline) {
            $message .= PHP_EOL;
        }

        $this->stream->write($message);
    }
}
