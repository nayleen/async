<?php

declare(strict_types = 1);

namespace Nayleen\Async\Component;

use Nayleen\Async\Kernel;

interface Recommender
{
    public function recommend(Kernel $kernel): void;
}
