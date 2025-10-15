<?php

declare(strict_types=1);

namespace Netgen\RemoteMedia\Tests\API\Upload;

use Netgen\RemoteMedia\API\Upload\FileStruct;
use Netgen\RemoteMedia\API\Upload\ResourceStruct;
use Netgen\RemoteMedia\API\Values\Folder;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

use function basename;

#[CoversClass(ResourceStruct::class)]
final class ResourceStructTest extends TestCase
{
    public function testSimpleCreate(): void
    {
        $fileStruct = FileStruct::fromPath('var/images/test.jpg');
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

        self::assertSame(
            'test.jpg',
            $resourceStruct->getFilename(),
        );

        self::assertNull($resourceStruct->getFolder());
        self::assertNull($resourceStruct->getFilenameOverride());
        self::assertFalse($resourceStruct->doOverwrite());
        self::assertFalse($resourceStruct->doInvalidate());
        self::assertNull($resourceStruct->getAltText());
        self::assertNull($resourceStruct->getCaption());
        self::assertEmpty($resourceStruct->getTags());
        self::assertEmpty($resourceStruct->getContext());
        self::assertFalse($resourceStruct->doHideFilename());
    }

    #[DataProvider('dataProvider')]
    public function testCreate(
        FileStruct $fileStruct,
        string $resourceType,
        ?Folder $folder,
        string $visibility,
        ?string $filenameOverride,
        bool $overwrite,
        bool $invalidate,
        ?string $altText,
        ?string $caption,
        array $tags,
        array $context,
        bool $hideFilename,
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
            $context,
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
            $filenameOverride ?? basename($fileStruct->getUri()),
            $resourceStruct->getFilename(),
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

        self::assertSame(
            $context,
            $resourceStruct->getContext(),
        );
    }

    public static function dataProvider(): array
    {
        return [
            [
                FileStruct::fromPath('var/images/test.jpg'),
                'image',
                Folder::fromPath('root/images'),
                'my_new_image.jpg',
                'public',
                true,
                false,
                'This is some alt text',
                'This is some caption',
                ['tag1', 'tag2'],
                [],
                true,
            ],
            [
                FileStruct::fromPath('var/images/test2.jpg'),
                'image',
                Folder::fromPath('root'),
                'private',
                null,
                true,
                true,
                null,
                null,
                [],
                [
                    'type' => 'product_image',
                ],
                false,
            ],
            [
                FileStruct::fromPath('var/videos/example.mp4'),
                'video',
                null,
                'protected',
                null,
                false,
                false,
                null,
                null,
                ['example'],
                [],
                true,
            ],
        ];
    }
}
