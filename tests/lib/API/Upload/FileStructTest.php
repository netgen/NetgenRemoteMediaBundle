<?php

declare(strict_types=1);

namespace Netgen\RemoteMedia\Tests\API\Upload;

use Netgen\RemoteMedia\API\Upload\FileStruct;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpFoundation\File\UploadedFile;

#[CoversClass(FileStruct::class)]
final class FileStructTest extends TestCase
{
    public function testFromUrl(): void
    {
        $fileStruct = FileStruct::fromUrl('https://example.com/images/sample.jpg');

        self::assertSame(
            'https://example.com/images/sample.jpg',
            $fileStruct->getUri(),
        );

        self::assertSame(
            'sample.jpg',
            $fileStruct->getOriginalFilename(),
        );

        self::assertSame(
            'jpg',
            $fileStruct->getOriginalExtension(),
        );

        self::assertSame(
            FileStruct::TYPE_EXTERNAL,
            $fileStruct->getType(),
        );

        self::assertFalse($fileStruct->isLocal());
        self::assertTrue($fileStruct->isExternal());
    }

    public function testFromPath(): void
    {
        $fileStruct = FileStruct::fromPath('/var/www/project/media/images/sample_image.jpg');

        self::assertSame(
            '/var/www/project/media/images/sample_image.jpg',
            $fileStruct->getUri(),
        );

        self::assertSame(
            'sample_image.jpg',
            $fileStruct->getOriginalFilename(),
        );

        self::assertSame(
            'jpg',
            $fileStruct->getOriginalExtension(),
        );

        self::assertSame(
            FileStruct::TYPE_LOCAL,
            $fileStruct->getType(),
        );

        self::assertTrue($fileStruct->isLocal());
        self::assertFalse($fileStruct->isExternal());
    }

    public function testFromUploadedFile(): void
    {
        $uploadedFile = $this->createMock(UploadedFile::class);

        $uploadedFile
            ->expects(self::once())
            ->method('getRealPath')
            ->willReturn('/var/www/project/media/images/sample_image.jpg');

        $uploadedFile
            ->expects(self::once())
            ->method('getClientOriginalName')
            ->willReturn('sample_image');

        $uploadedFile
            ->expects(self::once())
            ->method('getClientOriginalExtension')
            ->willReturn('jpg');

        $fileStruct = FileStruct::fromUploadedFile($uploadedFile);

        self::assertSame(
            '/var/www/project/media/images/sample_image.jpg',
            $fileStruct->getUri(),
        );

        self::assertSame(
            'sample_image',
            $fileStruct->getOriginalFilename(),
        );

        self::assertSame(
            'jpg',
            $fileStruct->getOriginalExtension(),
        );

        self::assertSame(
            FileStruct::TYPE_LOCAL,
            $fileStruct->getType(),
        );

        self::assertTrue($fileStruct->isLocal());
        self::assertFalse($fileStruct->isExternal());
    }
}
