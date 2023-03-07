<?php

declare(strict_types=1);

namespace Netgen\RemoteMedia\Tests\Exception;

use Netgen\RemoteMedia\Exception\NamedRemoteResourceLocationNotFoundException;
use PHPUnit\Framework\TestCase;

final class NamedRemoteResourceLocationNotFoundExceptionTest extends TestCase
{
    /**
     * @covers \Netgen\RemoteMedia\Exception\NamedRemoteResourceLocationNotFoundException::__construct
     */
    public function testException(): void
    {
        $this->expectException(NamedRemoteResourceLocationNotFoundException::class);
        $this->expectExceptionMessage('Named remote resource location with name "test_resource_location" not found.');

        throw new NamedRemoteResourceLocationNotFoundException('test_resource_location');
    }
}
