<?php

declare(strict_types = 1);

namespace Nayleen\Async\Console;

use Amp\ByteStream\WritableResourceStream;
use Amp\ByteStream\WritableStream;
use Symfony\Component\Console\Formatter\OutputFormatterInterface;
use Symfony\Component\Console\Output\Output;

use function Amp\Log\hasColorSupport;

class StreamOutput extends Output
{
    public function __construct(
        private readonly WritableStream $stream,
        ?int $verbosity = self::VERBOSITY_NORMAL,
        ?bool $decorated = null,
        ?OutputFormatterInterface $formatter = null,
    ) {
        $decorated ??= $this->hasColorSupport();

        parent::__construct($verbosity, $decorated, $formatter);
    }

    private function hasColorSupport(): bool
    {
        // respect https://no-color.org/ ENV variable
        if (isset($_SERVER['NO_COLOR']) || getenv('NO_COLOR') !== false) {
            return false;
        }

        // check for color support when writing to resource streams
        if ($this->stream instanceof WritableResourceStream) {
            return hasColorSupport();
        }

        // otherwise assume colors are unwanted
        return false;
    }

    protected function doWrite(string $message, bool $newline): void
    {
        if ($newline) {
            $message .= \PHP_EOL;
        }

        $this->stream->write($message);
    }
}
