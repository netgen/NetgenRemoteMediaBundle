<?php

declare(strict_types=1);

namespace Netgen\RemoteMedia\Tests\Exception;

use Netgen\RemoteMedia\Exception\RemoteResourceLocationNotFoundException;
use PHPUnit\Framework\TestCase;

final class RemoteResourceLocationNotFoundExceptionTest extends TestCase
{
    /**
     * @covers \Netgen\RemoteMedia\Exception\RemoteResourceLocationNotFoundException::__construct
     */
    public function testException(): void
    {
        $this->expectException(RemoteResourceLocationNotFoundException::class);
        $this->expectExceptionMessage('Remote resource location with ID "50" not found.');

        throw new RemoteResourceLocationNotFoundException(50);
    }
}
