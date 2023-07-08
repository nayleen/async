<?php

declare(strict_types = 1);

namespace Nayleen\Async\Console\Channel;

use Amp\Sync\Channel;
use PHPUnit\Framework\TestCase;

/**
 * @internal
 */
final class OutputTest extends TestCase
{
    /**
     * @test
     */
    public function writes_lines_to_given_channel(): void
    {
        $channel = $this->createMock(Channel::class);
        $channel->expects(self::once())->method('send')->with('test' . PHP_EOL);

        $output = new Output($channel);
        $output->writeln('test');
    }

    /**
     * @test
     */
    public function writes_to_given_channel(): void
    {
        $channel = $this->createMock(Channel::class);
        $channel->expects(self::once())->method('send')->with('test');

        $output = new Output($channel);
        $output->write('test');
    }
}
