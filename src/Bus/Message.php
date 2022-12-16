<?php

declare(strict_types = 1);

namespace Nayleen\Async\Bus;

interface Message
{
    public function name(): string;
}
