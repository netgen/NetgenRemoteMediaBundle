<?php

declare(strict_types=1);

namespace Netgen\RemoteMedia\Tests\Exception;

use Netgen\RemoteMedia\Exception\NotSupportedException;
use PHPUnit\Framework\TestCase;

final class NotSupportedExceptionTest extends TestCase
{
    /**
     * @covers \Netgen\RemoteMedia\Exception\NotSupportedException::__construct
     */
    public function testException(): void
    {
        $this->expectException(NotSupportedException::class);
        $this->expectExceptionMessage('Provider "cloudinary" does not support "folders".');

        throw new NotSupportedException('cloudinary', 'folders');
    }
}
