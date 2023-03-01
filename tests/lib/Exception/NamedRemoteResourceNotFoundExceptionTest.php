<?php

declare(strict_types=1);

namespace Netgen\RemoteMedia\Tests\Exception;

use Netgen\RemoteMedia\Exception\NamedRemoteResourceNotFoundException;
use PHPUnit\Framework\TestCase;

final class NamedRemoteResourceNotFoundExceptionTest extends TestCase
{
    /**
     * @covers \Netgen\RemoteMedia\Exception\NamedRemoteResourceNotFoundException::__construct
     */
    public function testException(): void
    {
        $this->expectException(NamedRemoteResourceNotFoundException::class);
        $this->expectExceptionMessage('Named remote resource with name "test_resource" not found.');

        throw new NamedRemoteResourceNotFoundException('test_resource');
    }
}
