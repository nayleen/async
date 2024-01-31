<?php

declare(strict_types = 1);

namespace Nayleen\Async\Task;

use Amp\Cancellation;
use Amp\Parallel\Worker\Task;
use Amp\Sync\Channel;

/**
 * @internal
 */
final readonly class AmpTask implements Task
{
    public function run(Channel $channel, Cancellation $cancellation): int
    {
        return 42;
    }
}
