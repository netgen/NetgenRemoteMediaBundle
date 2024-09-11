<?php

declare(strict_types=1);

namespace Netgen\RemoteMedia\Tests\Exception;

use Netgen\RemoteMedia\Exception\MimeTypeNotFoundException;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

#[CoversClass(MimeTypeNotFoundException::class)]
final class MimeTypeNotFoundExceptionTest extends TestCase
{
    public function testException(): void
    {
        $this->expectException(MimeTypeNotFoundException::class);
        $this->expectExceptionMessage('Mime type was not found for path "media/images/image.jpg" of type "img".');

        throw new MimeTypeNotFoundException('media/images/image.jpg', 'img');
    }
}
