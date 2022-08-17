<?php

declare(strict_types=1);

namespace Netgen\RemoteMedia\Tests\Exception;

use Netgen\RemoteMedia\Exception\RemoteResourceNotFoundException;
use PHPUnit\Framework\TestCase;

final class RemoteResourceNotFoundExceptionTest extends TestCase
{
    /**
     * @covers \Netgen\RemoteMedia\Exception\RemoteResourceNotFoundException::__construct
     */
    public function testException(): void
    {
        $this->expectException(RemoteResourceNotFoundException::class);
        $this->expectExceptionMessage('Remote resource with ID \'50\' not found.');

        throw new RemoteResourceNotFoundException('50');
    }
}
