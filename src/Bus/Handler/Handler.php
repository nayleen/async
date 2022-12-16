<?php

declare(strict_types = 1);

namespace Nayleen\Async\Bus\Handler;

use Amp\Promise;
use Nayleen\Async\Bus\Message;

interface Handler
{
    public function __invoke(Message $message): Promise;
}
