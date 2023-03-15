<?php

declare(strict_types=1);

namespace Netgen\RemoteMedia\Tests\Exception;

use Netgen\RemoteMedia\Exception\RemoteResourceNotFoundException;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

#[CoversClass(RemoteResourceNotFoundException::class)]
final class RemoteResourceNotFoundExceptionTest extends TestCase
{
    public function testException(): void
    {
        $this->expectException(RemoteResourceNotFoundException::class);
        $this->expectExceptionMessage('Remote resource with ID "50" not found.');

        throw new RemoteResourceNotFoundException('50');
    }
}
