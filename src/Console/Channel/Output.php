<?php

declare(strict_types = 1);

namespace Nayleen\Async\Console\Channel;

use Amp\Sync\Channel;
use Symfony\Component\Console\Formatter\OutputFormatterInterface;
use Symfony\Component\Console\Output\Output as BaseOutput;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * @api
 */
final class Output extends BaseOutput
{
    public function __construct(
        private readonly Channel $channel,
        ?int $verbosity = OutputInterface::VERBOSITY_NORMAL,
        bool $decorated = true,
        ?OutputFormatterInterface $formatter = null,
    ) {
        parent::__construct($verbosity, $decorated, $formatter);
    }

    public function __destruct()
    {
        $this->channel->close();
    }

    protected function doWrite(string $message, bool $newline): void
    {
        if ($newline) {
            $message .= PHP_EOL;
        }

        $this->channel->send($message);
    }
}
