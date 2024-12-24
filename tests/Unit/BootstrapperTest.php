<?php

declare(strict_types = 1);

namespace Nayleen\Async;

use Amp\ByteStream\ReadableStream;
use Amp\ByteStream\WritableStream;
use Amp\Dns\DnsResolver;
use Amp\Parallel\Context\ContextFactory;
use Amp\Parallel\Worker\WorkerFactory;
use Amp\Parallel\Worker\WorkerPool;
use Amp\Socket\ServerSocketFactory;
use Amp\Socket\SocketConnector;
use Closure;
use Monolog\ErrorHandler;
use Nayleen\Async\Component\TestCase as ComponentTestCase;
use Revolt\EventLoop;

/**
 * @internal
 * @small
 *
 * @covers \Nayleen\Async\Bootstrapper
 * @covers \Nayleen\Async\Component
 * @covers \Nayleen\Async\Component\Configuration\FileLoader
 * @covers \Nayleen\Async\Component\TestCase
 */
final class BootstrapperTest extends ComponentTestCase
{
    protected function component(): Component
    {
        return new Bootstrapper();
    }

    /**
     * @test
     */
    public function registers_core_parameters(): void
    {
        $this->assertContainerHasParameter('async.app_name', 'string');
        $this->assertContainerHasParameter('async.app_version', 'string');
        $this->assertContainerHasParameter('async.debug', 'bool');
        $this->assertContainerHasParameter('async.env', 'string');
        $this->assertContainerHasParameter('async.advisories', 'bool');
    }

    /**
     * @test
     */
    public function registers_core_services(): void
    {
        $this->assertContainerHasService('async.stderr', WritableStream::class);
        $this->assertContainerHasService('async.stdin', ReadableStream::class);
        $this->assertContainerHasService('async.stdout', WritableStream::class);
        $this->assertContainerHasService(IO::class);
    }

    /**
     * @test
     */
    public function registers_directory_parameters(): void
    {
        $this->assertContainerHasParameter('async.dir.base', 'string');
        $this->assertContainerHasParameter('async.dir.cache', 'string');
        $this->assertContainerHasParameter('async.dir.tmp', 'string');
    }

    /**
     * @test
     */
    public function registers_error_handler(): void
    {
        $this->assertContainerHasParameter('async.exceptions.handler', Closure::class);
        $this->assertContainerHasService(ErrorHandler::class);
    }

    /**
     * @test
     */
    public function registers_global_event_loop_services(): void
    {
        $this->assertContainerHasService(ContextFactory::class);
        $this->assertContainerHasService(DnsResolver::class);
        $this->assertContainerHasService(EventLoop\Driver::class);
        $this->assertContainerHasService(ServerSocketFactory::class);
        $this->assertContainerHasService(SocketConnector::class);
        $this->assertContainerHasService(WorkerFactory::class);
        $this->assertContainerHasService(WorkerPool::class);
    }
}
