<?php

declare(strict_types=1);

namespace Netgen\RemoteMedia\Tests\Exception;

use Netgen\RemoteMedia\Exception\TransformationHandlerNotFoundException;
use PHPUnit\Framework\TestCase;

final class TransformationHandlerNotFoundExceptionTest extends TestCase
{
    /**
     * @covers \Netgen\RemoteMedia\Exception\TransformationHandlerNotFoundException::__construct
     */
    public function testException(): void
    {
        $this->expectException(TransformationHandlerNotFoundException::class);
        $this->expectExceptionMessage('[NgRemoteMedia] Transformation handler with \'some_handler\' identifier for \'some_provider\' provider not found.');

        throw new TransformationHandlerNotFoundException('some_provider', 'some_handler');
    }
}
