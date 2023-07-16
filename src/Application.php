<?php

declare(strict_types = 1);

namespace Nayleen\Async;

use Amp\Cancellation;
use Amp\ForbidCloning;
use Amp\ForbidSerialization;
use Amp\Sync\Channel;

/**
 * @api
 */
abstract class Application extends Runtime
{
    use ForbidCloning;
    use ForbidSerialization;

    public function __construct(public readonly Tasks $tasks = new Tasks())
    {
    }

    protected function initialize(?Channel $channel, Cancellation $cancellation): Kernel
    {
        $kernel = parent::initialize($channel, $cancellation);
        $this->tasks->schedule($kernel->scheduler);

        return $kernel;
    }
}
