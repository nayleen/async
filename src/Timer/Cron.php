<?php

declare(strict_types = 1);

namespace Nayleen\Async\Timer;

use Cron\CronExpression;
use Nayleen\Async\Clock;
use Nayleen\Async\Kernel;
use Nayleen\Async\Timer;

/**
 * @api
 */
abstract class Cron extends Timer
{
    private Clock $clock;

    private readonly CronExpression $cronExpression;

    public function __construct(string $expression)
    {
        $this->cronExpression = CronExpression::factory($expression);
    }

    final protected function interval(): int
    {
        $next = $this->cronExpression->getNextRunDate(
            $now = $this->clock->now(),
            timeZone: $this->clock->timezone()->getName(),
        );

        return (int) $next->format('U') - (int) $now->format('U');
    }

    public function start(Kernel $kernel): void
    {
        parent::start($kernel);
        $this->clock = $kernel->clock();
    }
}
