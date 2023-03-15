<?php

declare(strict_types=1);

namespace Netgen\RemoteMedia\Tests\Exception;

use Netgen\RemoteMedia\Exception\RemoteResourceLocationNotFoundException;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

#[CoversClass(RemoteResourceLocationNotFoundException::class)]
final class RemoteResourceLocationNotFoundExceptionTest extends TestCase
{
    public function testException(): void
    {
        $this->expectException(RemoteResourceLocationNotFoundException::class);
        $this->expectExceptionMessage('Remote resource location with ID "50" not found.');

        throw new RemoteResourceLocationNotFoundException(50);
    }
}
