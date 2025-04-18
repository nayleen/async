<?php

declare(strict_types = 1);

namespace Nayleen\Async\Timer;

use Cron\CronExpression;
use Nayleen\Async\Timer;
use Override;

abstract readonly class Cron extends Timer
{
    private CronExpression $cronExpression;

    public function __construct(string $expression)
    {
        $this->cronExpression = new CronExpression($expression);
    }

    protected function interval(): float
    {
        $next = $this->cronExpression->getNextRunDate(
            $now = $this->kernel->clock()->now(),
            timeZone: $this->kernel->clock()->timezone()->getName(),
        );

        return (float) $next->format('U.v') - (float) $now->format('U.v');
    }

    #[Override]
    final protected function jitter(): float
    {
        return 0.0;
    }
}
