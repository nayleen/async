<?php

declare(strict_types = 1);

namespace Nayleen\Async\Recommender;

use Nayleen\Async\Kernel;
use Psr\Log\LogLevel;

use function Safe\ini_get;

/**
 * @internal
 */
final class Performance
{
    private const LOG_LEVEL = LogLevel::NOTICE;

    private const XDEBUG_DISABLED_MODES = ['', 'off'];

    public static function recommend(Kernel $kernel): void
    {
        if ($kernel->environment() === 'prod') {
            $log = fn (string $message, array $context = []) => $kernel->write(self::LOG_LEVEL, $message, $context);

            if (self::assertionsEnabled()) {
                $log('Running kernel in production mode with assertions enabled is not recommended');
                $log("You'll experience worse performance and see debugging log messages like this one");
                $log('Disable assertions (zend.assertions=-1) globally in php.ini or by passing it to your CLI options');
            }

            if (self::xdebugEnabled()) {
                $log('The "xdebug" extension is enabled, which has a major impact on performance');
            }
        }
    }

    private static function assertionsEnabled(): bool
    {
        return ini_get('zend.assertions') === '1';
    }

    private static function xdebugEnabled(): bool
    {
        // check for runtime environment variable first
        if (($envMode = getenv('XDEBUG_MODE')) !== false) {
            return !in_array($envMode, self::XDEBUG_DISABLED_MODES, true);
        }

        return !in_array(ini_get('xdebug.mode'), self::XDEBUG_DISABLED_MODES, true);
    }
}
