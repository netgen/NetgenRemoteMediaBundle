<?php

declare(strict_types=1);

namespace Netgen\RemoteMedia\Tests\Core;

use Netgen\RemoteMedia\Core\UploadFile;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpFoundation\File\UploadedFile;

final class UploadFileTest extends TestCase
{
    /**
     * @covers \Netgen\RemoteMedia\Core\UploadFile::fromUri
     */
    public function testFromUri(): void
    {
        $uri = '/var/www/remote-media/image.jpg';
        $uploadFile = UploadFile::fromUri($uri);

        self::assertSame($uri, $uploadFile->uri());
        self::assertSame('image', $uploadFile->originalFilename());
        self::assertSame('jpg', $uploadFile->originalExtension());
    }

    /**
     * @covers \Netgen\RemoteMedia\Core\UploadFile::fromUploadedFile
     */
    public function testFromUploadedFile(): void
    {
        $uri = '/var/www/remote-media/image.jpg';

        $fileMock = $this->createMock(UploadedFile::class);

        $fileMock
            ->expects(self::once())
            ->method('getRealPath')
            ->willReturn($uri);

        $fileMock
            ->expects(self::once())
            ->method('getClientOriginalName')
            ->willReturn('image');

        $fileMock
            ->expects(self::once())
            ->method('getClientOriginalExtension')
            ->willReturn('jpg');

        $uploadFile = UploadFile::fromUploadedFile($fileMock);

        self::assertSame($uri, $uploadFile->uri());
        self::assertSame('image', $uploadFile->originalFilename());
        self::assertSame('jpg', $uploadFile->originalExtension());
    }
}
