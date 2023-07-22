<?php

declare(strict_types = 1);

namespace Nayleen\Async\Bus;

interface Bus
{
    public function handle(Message $message): void;
}
