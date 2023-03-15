<?php

declare(strict_types=1);

namespace Netgen\RemoteMedia\Tests\Exception;

use Netgen\RemoteMedia\Exception\NamedRemoteResourceLocationNotFoundException;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

#[CoversClass(NamedRemoteResourceLocationNotFoundException::class)]
final class NamedRemoteResourceLocationNotFoundExceptionTest extends TestCase
{
    public function testException(): void
    {
        $this->expectException(NamedRemoteResourceLocationNotFoundException::class);
        $this->expectExceptionMessage('Named remote resource location with name "test_resource_location" not found.');

        throw new NamedRemoteResourceLocationNotFoundException('test_resource_location');
    }
}
