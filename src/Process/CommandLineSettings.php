<?php

declare(strict_types = 1);

namespace Nayleen\Async\Process;

final class CommandLineSettings
{
    private const SETTINGS_ASSERTIONS = [
        'assert.active' => '1',
        'assert.bail' => '0',
        'assert.exception' => '1',
        'assert.warning' => '0',
        'zend.assertions' => '1',
    ];

    private const SETTINGS_XDEBUG = [
        'xdebug.mode' => 'debug',
        'xdebug.start_with_request' => 'default',
        'xdebug.client_port' => '9003',
        'xdebug.client_host' => 'localhost',
    ];

    /**
     * @param array<string, string> $settings
     * @return array<string, string>
     */
    public static function apply(array $settings)
    {
        // copy ini values set via the command line to the child process
        if (ini_get('zend.assertions') !== '-1') {
            foreach (self::SETTINGS_ASSERTIONS as $setting => $defaultValue) {
                $iniValue = ini_get($setting);
                $settings[$setting] = empty($iniValue) ? $defaultValue : $iniValue;
            }
        }

        if (ini_get('xdebug.mode') !== false) {
            foreach (self::SETTINGS_XDEBUG as $setting => $defaultValue) {
                $iniValue = ini_get($setting);
                $settings[$setting] = empty($iniValue) ? $defaultValue : $iniValue;
            }
        }

        ksort($settings, SORT_NATURAL);
        return $settings;
    }
}
