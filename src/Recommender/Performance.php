<?php

declare(strict_types = 1);

namespace Nayleen\Async\Recommender;

use Nayleen\Async\Kernel;
use Psr\Log\LogLevel;

/**
 * @internal
 */
final class Performance
{
    public static function recommend(Kernel $kernel): void
    {
        if ($kernel->environment() === 'prod') {
            $level = LogLevel::NOTICE;

            if (ini_get('zend.assertions') === '1') {
                $kernel->write(
                    $level,
                    'Running kernel in production mode with assertions enabled is not recommended',
                    ['loop_driver' => Kernel::class]
                );
                $kernel->write($level, "You'll experience worse performance and see debugging log messages");
                $kernel->write($level,
                    'Disable assertions globally in php.ini (zend.assertions = -1) '
                    . 'or by passing it to your CLI options (-d zend.assertions=-1)'
                );
            }

            if (!in_array(ini_get('xdebug.mode'), [false, 'off'], true)) {
                $kernel->write($level, 'The "xdebug" extension is enabled, which has a major impact on performance');
            }
        }
    }
}
