<?php

declare(strict_types = 1);

namespace Nayleen\Async\Kernel;

use Nayleen\Async\Kernel;
use Psr\Log\LoggerInterface;

/**
 * @internal
 */
final class PerformanceRecommender
{
    public static function execute(LoggerInterface $logger, string $environment): void
    {
        if ($environment === 'prod') {
            if (ini_get('zend.assertions') === '1') {
                $logger->notice(
                    sprintf(
                        'Running %s in production mode with assertions enabled is not recommended. '
                        . "You'll see internal debugging log messages and get worse performance; "
                        . 'Disable assertions globally in php.ini (zend.assertions = -1) '
                        . 'or by passing it to your CLI options (-d zend.assertions=-1).',
                        Kernel::class,
                    ),
                );
            }

            if (!in_array(ini_get('xdebug.mode'), [false, 'off'], true)) {
                $logger->notice('The "xdebug" extension is enabled, which has a major impact on performance.');
            }
        }
    }
}
