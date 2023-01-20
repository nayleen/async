<?php

declare(strict_types = 1);

namespace Nayleen\Async\Runner;

final class Factory
{
    private const DEFAULT_SETTINGS_ASSERTIONS = [
        'assert.active' => '1',
        'assert.bail' => '0',
        'assert.exception' => '1',
        'assert.warning' => '0',
        'zend.assertions' => '1',
    ];

    private const DEFAULT_SETTINGS_PHP = [
        'display_errors' => '0',
        'html_errors' => '0',
        'log_errors' => '1',
    ];

    private const DEFAULT_SETTINGS_XDEBUG = [
        'xdebug.client_host' => 'localhost',
        'xdebug.client_port' => '9003',
        'xdebug.mode' => 'debug',
        'xdebug.start_with_request' => 'default',
    ];

    /**
     * @return list<string>
     */
    private function settings(): array
    {
        $settings = array_replace(self::DEFAULT_SETTINGS_PHP, $settings);

        // copy ini values set via the command line to the child process
        if (ini_get('zend.assertions') !== '-1') {
            foreach (self::DEFAULT_SETTINGS_ASSERTIONS as $setting => $defaultValue) {
                $iniValue = ini_get($setting);
                $settings[$setting] = empty($iniValue) ? $defaultValue : $iniValue;
            }
        }

        if (ini_get('xdebug.mode') !== false) {
            foreach (self::DEFAULT_SETTINGS_XDEBUG as $setting => $defaultValue) {
                $iniValue = ini_get($setting);
                $settings[$setting] = empty($iniValue) ? $defaultValue : $iniValue;
            }
        }

        ksort($settings, SORT_NATURAL);

        $result = [];
        foreach ($settings as $setting => $value) {
            $result[] = sprintf('-d%s=%s', $setting, $value);
        }

        return $result;
    }
}
