<?php

declare(strict_types = 1);

namespace Nayleen\Async\Bus;

use Amp\Promise;

interface Bus
{
    public function handle(Message $message): Promise;
}
