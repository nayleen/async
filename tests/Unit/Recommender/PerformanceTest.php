<?php

declare(strict_types = 1);

namespace Nayleen\Async\Recommender;

use Amp\PHPUnit\AsyncTestCase;
use Monolog\Handler\TestHandler;
use Monolog\Logger;
use Nayleen\Async\Environment;
use Nayleen\Async\Test\TestKernel;

/**
 * @internal
 * @backupGlobals enabled
 */
final class PerformanceTest extends AsyncTestCase
{
    /**
     * @test
     */
    public function logs_nothing_in_non_production_mode(): void
    {
        $logger = new Logger('test');
        $logger->pushHandler($handler = new TestHandler());

        $kernel = TestKernel::create()
            ->withDependency('async.env', 'dev')
            ->withDependency(Logger::class, $logger);

        Performance::recommend($kernel);

        self::assertFalse($handler->hasNoticeRecords());
    }

    /**
     * @test
     */
    public function logs_only_performance_recommendations_in_production_mode(): void
    {
        Environment::set('XDEBUG_MODE', 'off');

        $logger = new Logger('test');
        $logger->pushHandler($handler = new TestHandler());

        $kernel = TestKernel::create()
            ->withDependency('async.env', 'prod')
            ->withDependency(Logger::class, $logger);

        Performance::recommend($kernel);

        self::assertTrue($handler->hasNoticeThatContains('Running kernel in production mode with assertions enabled is not recommended'));
        self::assertTrue($handler->hasNoticeThatContains("You'll experience worse performance and see debugging log messages like this one"));
        self::assertTrue($handler->hasNoticeThatContains('Set zend.assertions = -1 globally in php.ini or by passing it to your CLI options'));
    }

    /**
     * @test
     */
    public function logs_xdebug_being_enabled(): void
    {
        Environment::set('XDEBUG_MODE', 'debug');

        $logger = new Logger('test');
        $logger->pushHandler($handler = new TestHandler());

        $kernel = TestKernel::create()
            ->withDependency('async.env', 'prod')
            ->withDependency(Logger::class, $logger);

        Performance::recommend($kernel);

        self::assertTrue($handler->hasNoticeThatContains('The "xdebug" extension is enabled, which has a major impact on performance'));
    }

    /**
     * @test
     */
    public function logs_xdebug_being_enabled_in_ini_settings(): void
    {
        Environment::set('XDEBUG_MODE', null);

        $logger = new Logger('test');
        $logger->pushHandler($handler = new TestHandler());

        $kernel = TestKernel::create()
            ->withDependency('async.env', 'prod')
            ->withDependency(Logger::class, $logger);

        Performance::recommend($kernel);

        self::assertTrue($handler->hasNoticeThatContains('The "xdebug" extension is enabled, which has a major impact on performance'));
    }
}
