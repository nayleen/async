<?php

declare(strict_types = 1);

namespace Nayleen\Async;

use Amp\ByteStream\ReadableStream;
use Amp\ByteStream\WritableStream;
use Closure;
use Monolog\ErrorHandler;
use Nayleen\Async\Component\TestCase as ComponentTestCase;
use Revolt\EventLoop;

/**
 * @internal
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
    }

    /**
     * @test
     */
    public function registers_core_services(): void
    {
        $this->assertContainerHasService('async.stderr', WritableStream::class);
        $this->assertContainerHasService('async.stdin', ReadableStream::class);
        $this->assertContainerHasService('async.stdout', WritableStream::class);
        $this->assertContainerHasService(EventLoop\Driver::class);
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
        $this->assertContainerHasParameter('async.exception_handler', Closure::class);
        $this->assertContainerHasService(ErrorHandler::class);
    }
}
