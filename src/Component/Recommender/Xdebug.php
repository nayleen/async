<?php

declare(strict_types = 1);

namespace Nayleen\Async\Component\Recommender;

use Nayleen\Async\Component\Recommender;
use Nayleen\Async\Kernel;
use Safe;

/**
 * @psalm-internal Nayleen\Async
 */
final class Xdebug implements Recommender
{
    private const XDEBUG_DISABLED_MODES = ['', 'off'];

    public function __construct() {}

    private function xdebugEnabled(): bool
    {
        // check for runtime environment variable first
        $envSetting = $_ENV['XDEBUG_MODE'] ?? $_SERVER['XDEBUG_MODE'] ?? null;

        if (isset($envSetting)) {
            return !in_array($envSetting, self::XDEBUG_DISABLED_MODES, true);
        }

        return !in_array(Safe\ini_get('xdebug.mode'), self::XDEBUG_DISABLED_MODES, true);
    }

    public function recommend(Kernel $kernel): void
    {
        if ($this->xdebugEnabled()) {
            $kernel->io()->notice('The "xdebug" extension is enabled, which has a major impact on performance');
        }
    }
}
