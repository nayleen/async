<?php

declare(strict_types = 1);

namespace Nayleen\Async\Component;

use Nayleen\Async\Kernel;

interface Advisory
{
    public function advise(Kernel $kernel): void;
}
