<?php

declare(strict_types=1);

namespace Netgen\RemoteMedia\Tests\API\Upload;

use Netgen\RemoteMedia\API\Upload\FileStruct;
use Netgen\RemoteMedia\API\Upload\ResourceStruct;
use Netgen\RemoteMedia\API\Values\Folder;
use Netgen\RemoteMedia\API\Values\RemoteResource;
use PHPUnit\Framework\TestCase;

final class ResourceStructTest extends TestCase
{
    /**
     * @covers \Netgen\RemoteMedia\API\Upload\ResourceStruct::__construct
     * @covers \Netgen\RemoteMedia\API\Upload\ResourceStruct::doInvalidate
     * @covers \Netgen\RemoteMedia\API\Upload\ResourceStruct::doOverwrite
     * @covers \Netgen\RemoteMedia\API\Upload\ResourceStruct::getAltText
     * @covers \Netgen\RemoteMedia\API\Upload\ResourceStruct::getCaption
     * @covers \Netgen\RemoteMedia\API\Upload\ResourceStruct::getFilenameOverride
     * @covers \Netgen\RemoteMedia\API\Upload\ResourceStruct::getFileStruct
     * @covers \Netgen\RemoteMedia\API\Upload\ResourceStruct::getFolder
     * @covers \Netgen\RemoteMedia\API\Upload\ResourceStruct::getResourceType
     * @covers \Netgen\RemoteMedia\API\Upload\ResourceStruct::getTags
     * @covers \Netgen\RemoteMedia\API\Upload\ResourceStruct::getVisibility
     */
    public function testSimpleCreate(): void
    {
        $fileStruct = FileStruct::fromUri('var/images/test.jpg');
        $resourceStruct = new ResourceStruct($fileStruct);

        self::assertSame(
            $fileStruct,
            $resourceStruct->getFileStruct(),
        );

        self::assertSame(
            'auto',
            $resourceStruct->getResourceType(),
        );

        self::assertSame(
            'public',
            $resourceStruct->getVisibility(),
        );

        self::assertNull($resourceStruct->getFolder());
        self::assertNull($resourceStruct->getFilenameOverride());
        self::assertFalse($resourceStruct->doOverwrite());
        self::assertFalse($resourceStruct->doInvalidate());
        self::assertNull($resourceStruct->getAltText());
        self::assertNull($resourceStruct->getCaption());
        self::assertEmpty($resourceStruct->getTags());
    }

    /**
     * @covers \Netgen\RemoteMedia\API\Upload\ResourceStruct::__construct
     * @covers \Netgen\RemoteMedia\API\Upload\ResourceStruct::doInvalidate
     * @covers \Netgen\RemoteMedia\API\Upload\ResourceStruct::doOverwrite
     * @covers \Netgen\RemoteMedia\API\Upload\ResourceStruct::getAltText
     * @covers \Netgen\RemoteMedia\API\Upload\ResourceStruct::getCaption
     * @covers \Netgen\RemoteMedia\API\Upload\ResourceStruct::getFilenameOverride
     * @covers \Netgen\RemoteMedia\API\Upload\ResourceStruct::getFileStruct
     * @covers \Netgen\RemoteMedia\API\Upload\ResourceStruct::getFolder
     * @covers \Netgen\RemoteMedia\API\Upload\ResourceStruct::getResourceType
     * @covers \Netgen\RemoteMedia\API\Upload\ResourceStruct::getTags
     * @covers \Netgen\RemoteMedia\API\Upload\ResourceStruct::getVisibility
     *
     * @dataProvider dataProvider
     */
    public function testCreate(
        FileStruct $fileStruct,
        string $resourceType = 'auto',
        ?Folder $folder = null,
        string $visibility = RemoteResource::VISIBILITY_PUBLIC,
        ?string $filenameOverride = null,
        bool $overwrite = false,
        bool $invalidate = false,
        ?string $altText = null,
        ?string $caption = null,
        array $tags = []
    ): void {
        $resourceStruct = new ResourceStruct(
            $fileStruct,
            $resourceType,
            $folder,
            $visibility,
            $filenameOverride,
            $overwrite,
            $invalidate,
            $altText,
            $caption,
            $tags,
        );

        self::assertSame(
            $fileStruct,
            $resourceStruct->getFileStruct(),
        );

        self::assertSame(
            $resourceType,
            $resourceStruct->getResourceType(),
        );

        self::assertSame(
            $folder,
            $resourceStruct->getFolder(),
        );

        self::assertSame(
            $filenameOverride,
            $resourceStruct->getFilenameOverride(),
        );

        self::assertSame(
            $overwrite,
            $resourceStruct->doOverwrite(),
        );

        self::assertSame(
            $invalidate,
            $resourceStruct->doInvalidate(),
        );

        self::assertSame(
            $visibility,
            $resourceStruct->getVisibility(),
        );

        self::assertSame(
            $altText,
            $resourceStruct->getAltText(),
        );

        self::assertSame(
            $caption,
            $resourceStruct->getCaption(),
        );

        self::assertSame(
            $tags,
            $resourceStruct->getTags(),
        );
    }

    public function dataProvider(): array
    {
        return [
            [
                FileStruct::fromUri('var/images/test.jpg'),
                'image',
                Folder::fromPath('root/images'),
                'my_new_image.jpg',
                'public',
                true,
                false,
                'This is some alt text',
                'This is some caption',
                ['tag1', 'tag2'],
            ],
            [
                FileStruct::fromUri('var/images/test2.jpg'),
                'image',
                Folder::fromPath('root'),
                'private',
                null,
                true,
                true,
                null,
                null,
                [],
            ],
            [
                FileStruct::fromUri('var/videos/example.mp4'),
                'video',
                null,
                'protected',
                null,
                false,
                false,
                null,
                null,
                ['example'],
            ],
        ];
    }
}
