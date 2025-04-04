<?php

declare(strict_types = 1);

namespace Nayleen\Async\Component;

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
use Nayleen\Async\Component;
use Nayleen\Async\Component\TestCase as ComponentTestCase;
use Nayleen\Async\IO;
use Revolt\EventLoop;

/**
 * @internal
 * @small
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
        $this->assertContainerHasParameter('async.advisories', 'bool');
        $this->assertContainerHasParameter('async.app.name', 'string');
        $this->assertContainerHasParameter('async.app.version', 'string');
        $this->assertContainerHasParameter('async.debug', 'bool');
        $this->assertContainerHasParameter('async.env', 'string');
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
