<?php

declare(strict_types=1);

namespace Netgen\Bundle\RemoteMediaBundle\Tests\RemoteMedia;

use eZ\Publish\Core\FieldType\Image\Value;
use eZHTTPFile;
use Netgen\Bundle\RemoteMediaBundle\RemoteMedia\UploadFile;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpFoundation\File\UploadedFile;

class UploadFileTest extends TestCase
{
    public function testFromUri(): void
    {
        $uri = '/var/www/remote-media/image.jpg';
        $uploadFile = UploadFile::fromUri($uri);

        self::assertEquals($uri, $uploadFile->uri());
        self::assertEquals('image', $uploadFile->originalFilename());
        self::assertEquals('jpg', $uploadFile->originalExtension());
    }

    public function testFromZHTTPFile(): void
    {
        $eZHTTPFile = new eZHTTPFile(
            'upload_image',
            [
                'name' => 'image.jpg',
                'type' => 'image/jpg',
                'tmp_name' => 'oji939i.jpg',
                'size' => 100,
            ],
        );

        $uploadFile = UploadFile::fromZHTTPFile($eZHTTPFile);

        self::assertEquals('oji939i.jpg', $uploadFile->uri());
        self::assertEquals('image', $uploadFile->originalFilename());
        self::assertEquals('jpg', $uploadFile->originalExtension());
    }

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

        self::assertEquals($uri, $uploadFile->uri());
        self::assertEquals('image', $uploadFile->originalFilename());
        self::assertEquals('jpg', $uploadFile->originalExtension());
    }

    public function testFromEzImageValue(): void
    {
        $webRoot = '/var/www/remote-media';
        $uri = '/image.jpg';

        $value = new Value([
            'uri' => $uri,
            'fileName' => 'image.jpg',
        ]);

        $uploadFile = UploadFile::fromEzImageValue($value, $webRoot);

        self::assertEquals($webRoot . $uri, $uploadFile->uri());
        self::assertEquals('image', $uploadFile->originalFilename());
        self::assertEquals('jpg', $uploadFile->originalExtension());
    }
}
