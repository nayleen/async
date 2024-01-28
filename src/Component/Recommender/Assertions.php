<?php

declare(strict_types = 1);

namespace Nayleen\Async\Component\Recommender;

use Nayleen\Async\Component\Recommender;
use Nayleen\Async\Kernel;
use Safe;

/**
 * @psalm-internal Nayleen\Async
 */
final class Assertions implements Recommender
{
    public function __construct() {}

    private function assertionsEnabled(): bool
    {
        return Safe\ini_get('zend.assertions') === '1';
    }

    public function recommend(Kernel $kernel): void
    {
        if ($this->assertionsEnabled()) {
            $kernel->io()->notice('Running Nayleen\Async\Kernel with assertions enabled is not recommended');
            $kernel->io()->notice("You'll experience worse performance and see debugging log messages like this one");
            $kernel->io()->notice('Set zend.assertions=-1 globally (i.e. in php.ini) or by passing it to your CLI options');
        }
    }
}
