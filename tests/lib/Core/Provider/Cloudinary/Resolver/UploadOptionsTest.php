<?php

declare(strict_types=1);

namespace Netgen\RemoteMedia\Tests\Core\Provider\Cloudinary\Resolver;

use Netgen\RemoteMedia\API\Upload\FileStruct;
use Netgen\RemoteMedia\API\Upload\ResourceStruct;
use Netgen\RemoteMedia\API\Values\Folder;
use Netgen\RemoteMedia\Core\Provider\Cloudinary\Resolver\UploadOptions as UploadOptionsResolver;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Mime\MimeTypesInterface;

use function pathinfo;
use function str_replace;

use const PATHINFO_DIRNAME;
use const PATHINFO_EXTENSION;
use const PATHINFO_FILENAME;

final class UploadOptionsTest extends TestCase
{
    protected UploadOptionsResolver $resolver;

    protected MockObject $mimeTypes;

    protected function setUp(): void
    {
        $this->mimeTypes = $this->createMock(MimeTypesInterface::class);

        $this->resolver = new UploadOptionsResolver(
            ['image', 'video'],
            $this->mimeTypes,
        );
    }

    /**
     * @covers \Netgen\RemoteMedia\Core\Provider\Cloudinary\Resolver\UploadOptions::__construct
     * @covers \Netgen\RemoteMedia\Core\Provider\Cloudinary\Resolver\UploadOptions::appendExtension
     * @covers \Netgen\RemoteMedia\Core\Provider\Cloudinary\Resolver\UploadOptions::parseMimeCategory
     * @covers \Netgen\RemoteMedia\Core\Provider\Cloudinary\Resolver\UploadOptions::resolve
     * @dataProvider dataProvider
     */
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

        if (!$resourceStruct->doOverwrite()) {
            $extension = pathinfo($options['public_id'], PATHINFO_EXTENSION);
            $basename = pathinfo($options['public_id'], PATHINFO_FILENAME);

            if (pathinfo($options['public_id'], PATHINFO_DIRNAME) !== '.') {
                $basename = pathinfo($options['public_id'], PATHINFO_DIRNAME) . '/' . $basename;
            }

            $extension = $extension ? '.' . $extension : '';

            self::assertMatchesRegularExpression(
                '/^' . str_replace('/', '\/', $basename) . '_[\w]{10}' . $extension . '$/',
                $resolvedOptions['public_id'],
            );

            unset($options['public_id'], $resolvedOptions['public_id']);
        }

        self::assertSame($options, $resolvedOptions);
    }

    public function dataProvider(): array
    {
        return [
            [
                new ResourceStruct(
                    FileStruct::fromUri('test_image_.jpg'),
                ),
                'image/jpg',
                [
                    'public_id' => 'test_image',
                    'overwrite' => false,
                    'invalidate' => false,
                    'discard_original_filename' => true,
                    'context' => [
                        'alt' => '',
                        'caption' => '',
                    ],
                    'resource_type' => 'auto',
                    'tags' => [],
                ],
            ],
            [
                new ResourceStruct(
                    FileStruct::fromUri('_test_!"#$%&()=?*image_.jpg'),
                ),
                'image/jpg',
                [
                    'public_id' => '_test_image',
                    'overwrite' => false,
                    'invalidate' => false,
                    'discard_original_filename' => true,
                    'context' => [
                        'alt' => '',
                        'caption' => '',
                    ],
                    'resource_type' => 'auto',
                    'tags' => [],
                ],
            ],
            [
                new ResourceStruct(
                    FileStruct::fromUri('/var/storage/backup.zip'),
                    'raw',
                    Folder::fromPath('files/backups'),
                    'latest_backup.zip',
                    false,
                    false,
                    null,
                    null,
                    ['backup'],
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
                    ],
                    'resource_type' => 'raw',
                    'tags' => ['backup'],
                ],
            ],
            [
                new ResourceStruct(
                    FileStruct::fromUri('/var/storage/backup.zip'),
                    'raw',
                    Folder::fromPath('files/backups'),
                    'latest_backup.zip',
                    true,
                    true,
                    null,
                    null,
                    ['backup', 'archive'],
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
                    ],
                    'resource_type' => 'raw',
                    'tags' => ['backup', 'archive'],
                ],
            ],
            [
                new ResourceStruct(
                    FileStruct::fromUri('/var/storage/backup.zip'),
                ),
                'raw',
                [
                    'public_id' => 'backup',
                    'overwrite' => false,
                    'invalidate' => false,
                    'discard_original_filename' => true,
                    'context' => [
                        'alt' => '',
                        'caption' => '',
                    ],
                    'resource_type' => 'auto',
                    'tags' => [],
                ],
            ],
            [
                new ResourceStruct(
                    FileStruct::fromUri('/var/storage/media/example.mp4'),
                    'video',
                    Folder::fromPath('videos'),
                    null,
                    true,
                    true,
                    'This video shows an example',
                    'Example video',
                ),
                'video/mp4',
                [
                    'public_id' => 'videos/example',
                    'overwrite' => true,
                    'invalidate' => true,
                    'discard_original_filename' => true,
                    'context' => [
                        'alt' => 'This video shows an example',
                        'caption' => 'Example video',
                    ],
                    'resource_type' => 'video',
                    'tags' => [],
                ],
            ],
            [
                new ResourceStruct(
                    FileStruct::fromUri('/var/storage/media/example.mp4'),
                    'auto',
                    Folder::fromPath('videos'),
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
                    ],
                    'resource_type' => 'auto',
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
                    ],
                    'resource_type' => 'auto',
                    'tags' => [],
                ],
                false,
            ],
        ];
    }
}
