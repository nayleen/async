<?php

declare(strict_types = 1);

namespace Nayleen\Async\Component\Advisory;

use Nayleen\Async\Component\Advisory;
use Nayleen\Async\Kernel;
use Safe;

/**
 * @psalm-internal Nayleen\Async
 */
final readonly class Xdebug implements Advisory
{
    private string $iniSetting;

    /**
     * @var string[]
     */
    private const array XDEBUG_DISABLED_MODES = ['', 'off'];

    public function __construct(?string $iniSetting = null)
    {
        $this->iniSetting = $iniSetting ?? Safe\ini_get('xdebug.mode');
    }

    private function xdebugEnabled(): bool
    {
        // check for runtime environment variable first
        $envSetting = $_ENV['XDEBUG_MODE'] ?? $_SERVER['XDEBUG_MODE'] ?? null;

        if (isset($envSetting)) {
            return !in_array($envSetting, self::XDEBUG_DISABLED_MODES, true);
        }

        return !in_array($this->iniSetting, self::XDEBUG_DISABLED_MODES, true);
    }

    public function advise(Kernel $kernel): void
    {
        if ($this->xdebugEnabled()) {
            $kernel->io()->notice('The "xdebug" extension is active, which has a major impact on performance');
        }
    }
}
