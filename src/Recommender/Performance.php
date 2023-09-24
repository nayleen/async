<?php

declare(strict_types = 1);

namespace Nayleen\Async\Recommender;

use Nayleen\Async\Environment;
use Nayleen\Async\Kernel;
use Safe;

/**
 * @internal
 */
final class Performance
{
    private const XDEBUG_DISABLED_MODES = ['', 'off'];

    public static function recommend(Kernel $kernel): void
    {
        if ($kernel->environment() === 'prod') {
            if (self::assertionsEnabled()) {
                $kernel->io()->notice('Running kernel in production mode with assertions enabled is not recommended');
                $kernel->io()->notice("You'll experience worse performance and see debugging log messages like this one");
                $kernel->io()->notice('Set zend.assertions = -1 globally in php.ini or by passing it to your CLI options');
            }

            if (self::xdebugEnabled()) {
                $kernel->io()->notice('The "xdebug" extension is enabled, which has a major impact on performance');
            }
        }
    }

    private static function assertionsEnabled(): bool
    {
        return Safe\ini_get('zend.assertions') === '1';
    }

    private static function xdebugEnabled(): bool
    {
        // check for runtime environment variable first
        $envSetting = Environment::get('XDEBUG_MODE', null);

        if (isset($envSetting)) {
            return !in_array($envSetting, self::XDEBUG_DISABLED_MODES, true);
        }

        return !in_array(Safe\ini_get('xdebug.mode'), self::XDEBUG_DISABLED_MODES, true);
    }
}
