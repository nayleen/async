<?php

declare(strict_types = 1);

namespace Nayleen\Async\Test;

use Amp\Cancellation;
use Amp\Parallel\Worker\Task as TaskInterface;
use Amp\Sync\Channel;

class Task implements TaskInterface
{
    public function run(Channel $channel, Cancellation $cancellation): mixed
    {
        return 420;
    }
}
