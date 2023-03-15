<?php

declare(strict_types=1);

namespace Netgen\RemoteMedia\Tests\Core\Provider\Cloudinary\Resolver;

use Netgen\RemoteMedia\API\Upload\FileStruct;
use Netgen\RemoteMedia\API\Upload\ResourceStruct;
use Netgen\RemoteMedia\API\Values\Folder;
use Netgen\RemoteMedia\Core\Provider\Cloudinary\Converter\VisibilityType as VisibilityTypeConverter;
use Netgen\RemoteMedia\Core\Provider\Cloudinary\Resolver\UploadOptions as UploadOptionsResolver;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Mime\MimeTypesInterface;

#[CoversClass(UploadOptionsResolver::class)]
final class UploadOptionsTest extends TestCase
{
    protected UploadOptionsResolver $resolver;

    protected MockObject $mimeTypes;

    protected function setUp(): void
    {
        $this->mimeTypes = $this->createMock(MimeTypesInterface::class);

        $this->resolver = new UploadOptionsResolver(
            new VisibilityTypeConverter(),
            ['image', 'video'],
            $this->mimeTypes,
        );
    }

    #[DataProvider('dataProvider')]
    public function testResolve(ResourceStruct $resourceStruct, string $mimeType, array $options, bool $hasExtension = true): void
    {
        if ($hasExtension) {
            $this->mimeTypes
                ->expects(self::once())
                ->method('guessMimeType')
                ->with($resourceStruct->getFileStruct()->getUri())
                ->willReturn($mimeType);
        }

        $resolvedOptions = $this->resolver->resolve($resourceStruct);

        self::assertSame($options, $resolvedOptions);
    }

    public static function dataProvider(): array
    {
        return [
            [
                new ResourceStruct(
                    FileStruct::fromUri('test_image_.jpg'),
                ),
                'image/jpg',
                [
                    'public_id' => 'test_image_jpg',
                    'overwrite' => false,
                    'invalidate' => false,
                    'discard_original_filename' => true,
                    'context' => [
                        'alt' => '',
                        'caption' => '',
                        'original_filename' => 'test_image_.jpg',
                    ],
                    'type' => 'upload',
                    'resource_type' => 'auto',
                    'access_mode' => 'public',
                    'access_control' => ['access_type' => 'anonymous'],
                    'tags' => [],
                ],
            ],
            [
                new ResourceStruct(
                    FileStruct::fromUri('_test_!"#$%&()=?*image_.jpg'),
                ),
                'image/jpg',
                [
                    'public_id' => '_test_image_jpg',
                    'overwrite' => false,
                    'invalidate' => false,
                    'discard_original_filename' => true,
                    'context' => [
                        'alt' => '',
                        'caption' => '',
                        'original_filename' => '_test_!"#$%&()=?*image_.jpg',
                    ],
                    'type' => 'upload',
                    'resource_type' => 'auto',
                    'access_mode' => 'public',
                    'access_control' => ['access_type' => 'anonymous'],
                    'tags' => [],
                ],
            ],
            [
                new ResourceStruct(
                    FileStruct::fromUri('/var/storage/backup.zip'),
                    'raw',
                    Folder::fromPath('files/backups'),
                    'private',
                    'latest_backup.zip',
                    false,
                    false,
                    null,
                    null,
                    ['backup'],
                    [
                        'alt' => 'test',
                        'original_filename' => 'something.jpg',
                        'type' => 'product_image',
                        'test' => 'test_value',
                    ],
                ),
                'application/zip',
                [
                    'public_id' => 'files/backups/latest_backup_zip.zip',
                    'overwrite' => false,
                    'invalidate' => false,
                    'discard_original_filename' => true,
                    'context' => [
                        'alt' => '',
                        'caption' => '',
                        'original_filename' => 'latest_backup.zip',
                        'type' => 'product_image',
                        'test' => 'test_value',
                    ],
                    'type' => 'private',
                    'resource_type' => 'raw',
                    'access_mode' => 'public',
                    'access_control' => ['access_type' => 'token'],
                    'tags' => ['backup'],
                ],
            ],
            [
                new ResourceStruct(
                    FileStruct::fromUri('/var/storage/backup.zip'),
                    'raw',
                    Folder::fromPath('files/backups'),
                    'protected',
                    'latest_backup.zip',
                    true,
                    true,
                    null,
                    null,
                    ['backup', 'archive'],
                    ['test', 'something'],
                ),
                'application/zip',
                [
                    'public_id' => 'files/backups/latest_backup_zip.zip',
                    'overwrite' => true,
                    'invalidate' => true,
                    'discard_original_filename' => true,
                    'context' => [
                        'alt' => '',
                        'caption' => '',
                        'original_filename' => 'latest_backup.zip',
                    ],
                    'type' => 'authenticated',
                    'resource_type' => 'raw',
                    'access_mode' => 'authenticated',
                    'access_control' => ['access_type' => 'token'],
                    'tags' => ['backup', 'archive'],
                ],
            ],
            [
                new ResourceStruct(
                    FileStruct::fromUri('/var/storage/backup.zip'),
                ),
                'raw',
                [
                    'public_id' => 'backup_zip',
                    'overwrite' => false,
                    'invalidate' => false,
                    'discard_original_filename' => true,
                    'context' => [
                        'alt' => '',
                        'caption' => '', 'original_filename' => 'backup.zip',
                    ],
                    'type' => 'upload',
                    'resource_type' => 'auto',
                    'access_mode' => 'public',
                    'access_control' => ['access_type' => 'anonymous'],
                    'tags' => [],
                ],
            ],
            [
                new ResourceStruct(
                    FileStruct::fromUri('/var/storage/media/example.mp4'),
                    'video',
                    Folder::fromPath('videos'),
                    'test',
                    null,
                    true,
                    true,
                    'This video shows an example',
                    'Example video',
                ),
                'video/mp4',
                [
                    'public_id' => 'videos/example_mp4',
                    'overwrite' => true,
                    'invalidate' => true,
                    'discard_original_filename' => true,
                    'context' => [
                        'alt' => 'This video shows an example',
                        'caption' => 'Example video',
                        'original_filename' => 'example.mp4',
                    ],
                    'type' => 'upload',
                    'resource_type' => 'video',
                    'access_mode' => 'public',
                    'access_control' => ['access_type' => 'anonymous'],
                    'tags' => [],
                ],
            ],
            [
                new ResourceStruct(
                    FileStruct::fromUri('/var/storage/media/example.mp4'),
                    'auto',
                    Folder::fromPath('videos'),
                    'protected',
                    'my video $%&/',
                    true,
                    true,
                ),
                'video/mp4',
                [
                    'public_id' => 'videos/my_video',
                    'overwrite' => true,
                    'invalidate' => true,
                    'discard_original_filename' => true,
                    'context' => [
                        'alt' => '',
                        'caption' => '',
                        'original_filename' => 'my video $%&/',
                    ],
                    'type' => 'authenticated',
                    'resource_type' => 'auto',
                    'access_mode' => 'authenticated',
                    'access_control' => ['access_type' => 'token'],
                    'tags' => [],
                ],
            ],
            [
                new ResourceStruct(
                    FileStruct::fromUri('/var/storage/media/no_extension_example'),
                    'auto',
                    Folder::fromPath('raw'),
                ),
                'raw',
                [
                    'public_id' => 'raw/no_extension_example',
                    'overwrite' => false,
                    'invalidate' => false,
                    'discard_original_filename' => true,
                    'context' => [
                        'alt' => '',
                        'caption' => '',
                        'original_filename' => 'no_extension_example',
                    ],
                    'type' => 'upload',
                    'resource_type' => 'auto',
                    'access_mode' => 'public',
                    'access_control' => ['access_type' => 'anonymous'],
                    'tags' => [],
                ],
                false,
            ],
        ];
    }
}
