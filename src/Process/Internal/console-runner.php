<?php

declare(strict_types = 1);

use Nayleen\Async\Kernel;
use Nayleen\Async\Runtime\Console;

(static function (): void {
    $paths = [
        dirname(__DIR__, 5) . '/autoload.php',
        dirname(__DIR__, 3) . '/vendor/autoload.php',
    ];

    foreach ($paths as $path) {
        if (file_exists($path)) {
            $autoloadPath = $path;
            break;
        }
    }

    if (!isset($autoloadPath)) {
        trigger_error(
            'Could not locate autoload.php in any of the following files: ' . implode(', ', $paths),
            E_USER_ERROR,
        );
    }

    /**
     * @psalm-suppress UnresolvableInclude
     */
    require $autoloadPath;
})();

(new Kernel())
    ->make(Console::class)
    ->run();
