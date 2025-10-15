<?php

declare(strict_types=1);

namespace Netgen\RemoteMedia\Tests\Core\Provider\Cloudinary\Resolver;

use Netgen\RemoteMedia\API\Upload\FileStruct;
use Netgen\RemoteMedia\API\Upload\ResourceStruct;
use Netgen\RemoteMedia\API\Values\Folder;
use Netgen\RemoteMedia\Core\Provider\Cloudinary\CloudinaryProvider;
use Netgen\RemoteMedia\Core\Provider\Cloudinary\Converter\VisibilityType as VisibilityTypeConverter;
use Netgen\RemoteMedia\Core\Provider\Cloudinary\Resolver\UploadOptions as UploadOptionsResolver;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

#[CoversClass(UploadOptionsResolver::class)]
final class UploadOptionsTest extends TestCase
{
    protected UploadOptionsResolver $fixedFolderModeResolver;

    protected UploadOptionsResolver $dynamicFolderModeResolver;

    protected function setUp(): void
    {
        $this->fixedFolderModeResolver = new UploadOptionsResolver(
            new VisibilityTypeConverter(),
            CloudinaryProvider::FOLDER_MODE_FIXED,
            true,
            false,
        );

        $this->dynamicFolderModeResolver = new UploadOptionsResolver(
            new VisibilityTypeConverter(),
            CloudinaryProvider::FOLDER_MODE_DYNAMIC,
            false,
            true,
        );
    }

    #[DataProvider('dataProvider')]
    public function testResolve(
        ResourceStruct $resourceStruct,
        array $options,
        string $folderMode = CloudinaryProvider::FOLDER_MODE_FIXED,
    ): void {
        $resolvedOptions = $folderMode === CloudinaryProvider::FOLDER_MODE_FIXED
            ? $this->fixedFolderModeResolver->resolve($resourceStruct)
            : $this->dynamicFolderModeResolver->resolve($resourceStruct);

        self::assertSame($options, $resolvedOptions);
    }

    public static function dataProvider(): iterable
    {
        return [
            [
                new ResourceStruct(
                    FileStruct::fromPath('test_image_.jpg'),
                ),
                [
                    'use_filename' => true,
                    'use_filename_as_display_name' => true,
                    'unique_filename' => false,
                    'filename_override' => 'test_image__jpg.jpg',
                    'overwrite' => false,
                    'invalidate' => false,
                    'context' => [
                        'alt' => '',
                        'caption' => '',
                        'original_filename' => 'test_image_.jpg',
                    ],
                    'type' => 'upload',
                    'resource_type' => 'auto',
                    'access_mode' => 'public',
                    'access_control' => [['access_type' => 'anonymous']],
                    'tags' => [],
                ],
                CloudinaryProvider::FOLDER_MODE_FIXED,
            ],
            [
                new ResourceStruct(
                    FileStruct::fromPath('_test_!"#$%&()=?*image_.jpg'),
                ),
                [
                    'use_filename' => true,
                    'use_filename_as_display_name' => true,
                    'unique_filename' => false,
                    'filename_override' => '_test_!"#$%&()=?*image__jpg.jpg',
                    'overwrite' => false,
                    'invalidate' => false,
                    'context' => [
                        'alt' => '',
                        'caption' => '',
                        'original_filename' => '_test_!"#$%&()=?*image_.jpg',
                    ],
                    'type' => 'upload',
                    'resource_type' => 'auto',
                    'access_mode' => 'public',
                    'access_control' => [['access_type' => 'anonymous']],
                    'tags' => [],
                ],
                CloudinaryProvider::FOLDER_MODE_FIXED,
            ],
            [
                new ResourceStruct(
                    FileStruct::fromPath('/var/storage/backup.zip'),
                    'raw',
                    Folder::fromPath('files/backups'),
                    'protected',
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
                [
                    'use_filename' => true,
                    'use_filename_as_display_name' => true,
                    'unique_filename' => false,
                    'filename_override' => 'latest_backup_zip.zip',
                    'overwrite' => false,
                    'invalidate' => false,
                    'context' => [
                        'alt' => '',
                        'caption' => '',
                        'original_filename' => 'latest_backup.zip',
                        'type' => 'product_image',
                        'test' => 'test_value',
                    ],
                    'type' => 'authenticated',
                    'resource_type' => 'raw',
                    'access_mode' => 'authenticated',
                    'access_control' => [['access_type' => 'token']],
                    'tags' => ['backup'],
                    'folder' => 'files/backups',
                ],
                CloudinaryProvider::FOLDER_MODE_FIXED,
            ],
            [
                new ResourceStruct(
                    FileStruct::fromPath('/var/storage/backup.zip'),
                    'raw',
                    Folder::fromPath('files/backups'),
                    'protected',
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
                [
                    'use_filename' => true,
                    'use_filename_as_display_name' => true,
                    'unique_filename' => true,
                    'filename_override' => 'latest_backup.zip',
                    'overwrite' => false,
                    'invalidate' => false,
                    'context' => [
                        'alt' => '',
                        'caption' => '',
                        'original_filename' => 'latest_backup.zip',
                        'type' => 'product_image',
                        'test' => 'test_value',
                    ],
                    'type' => 'authenticated',
                    'resource_type' => 'raw',
                    'access_mode' => 'authenticated',
                    'access_control' => [['access_type' => 'token']],
                    'tags' => ['backup'],
                    'asset_folder' => 'files/backups',
                ],
                CloudinaryProvider::FOLDER_MODE_DYNAMIC,
            ],
            [
                new ResourceStruct(
                    FileStruct::fromPath('/var/storage/backup.zip'),
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
                [
                    'use_filename' => true,
                    'use_filename_as_display_name' => true,
                    'unique_filename' => false,
                    'filename_override' => 'latest_backup_zip.zip',
                    'overwrite' => true,
                    'invalidate' => true,
                    'context' => [
                        'alt' => '',
                        'caption' => '',
                        'original_filename' => 'latest_backup.zip',
                    ],
                    'type' => 'authenticated',
                    'resource_type' => 'raw',
                    'access_mode' => 'authenticated',
                    'access_control' => [['access_type' => 'token']],
                    'tags' => ['backup', 'archive'],
                    'folder' => 'files/backups',
                ],
                CloudinaryProvider::FOLDER_MODE_FIXED,
            ],
            [
                new ResourceStruct(
                    FileStruct::fromPath('/var/storage/backup.zip'),
                ),
                [
                    'use_filename' => true,
                    'use_filename_as_display_name' => true,
                    'unique_filename' => true,
                    'filename_override' => 'backup.zip',
                    'overwrite' => false,
                    'invalidate' => false,
                    'context' => [
                        'alt' => '',
                        'caption' => '', 'original_filename' => 'backup.zip',
                    ],
                    'type' => 'upload',
                    'resource_type' => 'auto',
                    'access_mode' => 'public',
                    'access_control' => [['access_type' => 'anonymous']],
                    'tags' => [],
                ],
                CloudinaryProvider::FOLDER_MODE_DYNAMIC,
            ],
            [
                new ResourceStruct(
                    FileStruct::fromPath('/var/storage/media/example.mp4'),
                    'video',
                    Folder::fromPath('videos'),
                    'test',
                    null,
                    true,
                    true,
                    'This video shows an example',
                    'Example video',
                ),
                [
                    'use_filename' => true,
                    'use_filename_as_display_name' => true,
                    'unique_filename' => false,
                    'filename_override' => 'example_mp4.mp4',
                    'overwrite' => true,
                    'invalidate' => true,
                    'context' => [
                        'alt' => 'This video shows an example',
                        'caption' => 'Example video',
                        'original_filename' => 'example.mp4',
                    ],
                    'type' => 'upload',
                    'resource_type' => 'video',
                    'access_mode' => 'public',
                    'access_control' => [['access_type' => 'anonymous']],
                    'tags' => [],
                    'folder' => 'videos',
                ],
                CloudinaryProvider::FOLDER_MODE_FIXED,
            ],
            [
                new ResourceStruct(
                    FileStruct::fromPath('/var/storage/media/example.mp4'),
                    'auto',
                    Folder::fromPath('videos'),
                    'protected',
                    'my video $%&/',
                    true,
                    true,
                ),
                [
                    'use_filename' => true,
                    'use_filename_as_display_name' => true,
                    'unique_filename' => true,
                    'filename_override' => 'my video $%&/',
                    'overwrite' => true,
                    'invalidate' => true,
                    'context' => [
                        'alt' => '',
                        'caption' => '',
                        'original_filename' => 'my video $%&/',
                    ],
                    'type' => 'authenticated',
                    'resource_type' => 'auto',
                    'access_mode' => 'authenticated',
                    'access_control' => [['access_type' => 'token']],
                    'tags' => [],
                    'asset_folder' => 'videos',
                ],
                CloudinaryProvider::FOLDER_MODE_DYNAMIC,
            ],
            [
                new ResourceStruct(
                    FileStruct::fromPath('/var/storage/media/no_extension_example'),
                    'auto',
                    Folder::fromPath('raw'),
                ),
                [
                    'use_filename' => true,
                    'use_filename_as_display_name' => true,
                    'unique_filename' => false,
                    'filename_override' => 'no_extension_example',
                    'overwrite' => false,
                    'invalidate' => false,
                    'context' => [
                        'alt' => '',
                        'caption' => '',
                        'original_filename' => 'no_extension_example',
                    ],
                    'type' => 'upload',
                    'resource_type' => 'auto',
                    'access_mode' => 'public',
                    'access_control' => [['access_type' => 'anonymous']],
                    'tags' => [],
                    'folder' => 'raw',
                ],
                CloudinaryProvider::FOLDER_MODE_FIXED,
            ],
        ];
    }
}
