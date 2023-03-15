<?php

declare(strict_types=1);

namespace Netgen\RemoteMedia\Tests\Exception;

use Netgen\RemoteMedia\Exception\NotSupportedException;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

#[CoversClass(NotSupportedException::class)]
final class NotSupportedExceptionTest extends TestCase
{
    public function testException(): void
    {
        $this->expectException(NotSupportedException::class);
        $this->expectExceptionMessage('Provider "cloudinary" does not support "folders".');

        throw new NotSupportedException('cloudinary', 'folders');
    }
}
