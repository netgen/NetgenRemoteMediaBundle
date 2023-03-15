<?php

declare(strict_types=1);

namespace Netgen\RemoteMedia\Tests\Exception;

use Netgen\RemoteMedia\Exception\NamedRemoteResourceNotFoundException;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

#[CoversClass(NamedRemoteResourceNotFoundException::class)]
final class NamedRemoteResourceNotFoundExceptionTest extends TestCase
{
    public function testException(): void
    {
        $this->expectException(NamedRemoteResourceNotFoundException::class);
        $this->expectExceptionMessage('Named remote resource with name "test_resource" not found.');

        throw new NamedRemoteResourceNotFoundException('test_resource');
    }
}
