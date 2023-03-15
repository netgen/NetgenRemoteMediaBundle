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
    public function testFromUri(): void
    {
        $fileStruct = FileStruct::fromUri('/var/www/project/media/images/sample_image.jpg');

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
    }
}
