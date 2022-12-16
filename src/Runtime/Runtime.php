<?php

declare(strict_types = 1);

namespace Nayleen\Async\Runtime;

interface Runtime
{
    public function run(): int;
}
