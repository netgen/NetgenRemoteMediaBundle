<?php

declare(strict_types=1);

namespace Netgen\RemoteMedia\Tests\Core\Provider\Cloudinary\Factory;

use Netgen\RemoteMedia\API\Factory\FileHash as FileHashFactoryInterface;
use Netgen\RemoteMedia\API\Values\Folder;
use Netgen\RemoteMedia\API\Values\RemoteResource;
use Netgen\RemoteMedia\Core\Provider\Cloudinary\Converter\ResourceType as ResourceTypeConverter;
use Netgen\RemoteMedia\Core\Provider\Cloudinary\Converter\VisibilityType as VisibilityTypeConverter;
use Netgen\RemoteMedia\Core\Provider\Cloudinary\Factory\CloudinaryInstance;
use Netgen\RemoteMedia\Core\Provider\Cloudinary\Factory\RemoteResource as RemoteResourceFactory;
use Netgen\RemoteMedia\Exception\Factory\InvalidDataException;
use Netgen\RemoteMedia\Tests\AbstractTestCase;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\MockObject\MockObject;

#[CoversClass(RemoteResourceFactory::class)]
final class RemoteResourceTest extends AbstractTestCase
{
    protected RemoteResourceFactory $remoteResourceFactory;

    protected FileHashFactoryInterface|MockObject $fileHashFactoryMock;

    protected function setUp(): void
    {
        $this->fileHashFactoryMock = $this->createMock(FileHashFactoryInterface::class);

        $cloudinaryInstanceFactory = new CloudinaryInstance('myCloud', 'myKey', 'mySecret', 'https://api.cloudinary.com');
        $cloudinaryInstanceFactory->create();

        $this->remoteResourceFactory = new RemoteResourceFactory(
            new ResourceTypeConverter(),
            new VisibilityTypeConverter(),
            $this->fileHashFactoryMock,
        );
    }

    #[DataProvider('createDataProvider')]
    public function testCreate(array $cloudinaryResponse, RemoteResource $expectedResource): void
    {
        if (!($cloudinaryResponse['etag'] ?? null)) {
            $this->fileHashFactoryMock
                ->expects(self::once())
                ->method('createHash')
                ->with($cloudinaryResponse['secure_url'] ?? $cloudinaryResponse['url'])
                ->willReturn('a522f23sf81aa0afd03387c37e2b6eax');
        }

        $resource = $this->remoteResourceFactory->create($cloudinaryResponse);

        self::assertRemoteResourceSame(
            $expectedResource,
            $resource,
        );
    }

    public function testCreateMissingPublicId(): void
    {
        self::expectException(InvalidDataException::class);
        self::expectExceptionMessage('Missing required "public_id" property!');

        $this->remoteResourceFactory->create(['test' => 'test']);
    }

    public function testCreateMissingUrls(): void
    {
        self::expectException(InvalidDataException::class);
        self::expectExceptionMessage('Missing required "secure_url" or "url" property!');

        $this->remoteResourceFactory->create(['public_id' => 'test']);
    }

    public static function createDataProvider(): array
    {
        return [
            [
                [
                    'public_id' => 'c87hg9xfxrd4itiim3t0',
                    'version' => 1371995958,
                    'signature' => 'f8645b000be7d717599affc89a068157e4748276',
                    'width' => 864,
                    'height' => 576,
                    'format' => 'jpg',
                    'resource_type' => 'image',
                    'created_at' => '2013-06-23T13:59:18Z',
                    'bytes' => 120253,
                    'type' => 'upload',
                    'url' => 'http://res.cloudinary.com/myCloud/image/upload/v1371995958/c87hg9xfxrd4itiim3t0.jpg',
                    'secure_url' => 'https://res.cloudinary.com/myCloud/image/upload/v1371995958/c87hg9xfxrd4itiim3t0.jpg',
                    'etag' => 'e522f43cf89aa0afd03387c37e2b6e12',
                    'tags' => ['tag1'],
                    'overwritten' => 'true',
                    'context' => [
                        'custom' => [
                            'alt' => 'alt text',
                            'alt_text' => 'alt text',
                            'caption' => 'caption text',
                            'type' => 'product_image',
                            'source' => 'user_upload',
                        ],
                    ],
                    'variations' => [
                        'variation1',
                        'variation2',
                    ],
                ],
                new RemoteResource(
                    remoteId: 'upload|image|c87hg9xfxrd4itiim3t0',
                    type: 'image',
                    url: 'https://res.cloudinary.com/myCloud/image/upload/c87hg9xfxrd4itiim3t0',
                    md5: 'e522f43cf89aa0afd03387c37e2b6e12',
                    name: 'c87hg9xfxrd4itiim3t0',
                    version: '1371995958',
                    size: 120253,
                    altText: 'alt text',
                    caption: 'caption text',
                    tags: ['tag1'],
                    metadata: [
                        'signature' => 'f8645b000be7d717599affc89a068157e4748276',
                        'width' => 864,
                        'height' => 576,
                        'format' => 'jpg',
                        'created_at' => '2013-06-23T13:59:18Z',
                        'overwritten' => 'true',
                    ],
                    context: [
                        'type' => 'product_image',
                        'source' => 'user_upload',
                    ],
                ),
            ],
            [
                [
                    'public_id' => 'other/c87hg9xfxrd4itiim3t0',
                    'signature' => 'f8645b000be7d717599affc89a068157e4748276',
                    'format' => 'pdf',
                    'resource_type' => 'image',
                    'created_at' => '2013-06-23T13:59:18Z',
                    'bytes' => 120253,
                    'type' => 'upload',
                    'url' => 'http://res.cloudinary.com/myCloud/image/upload/v1371995958/other/c87hg9xfxrd4itiim3t0',
                    'secure_url' => 'https://res.cloudinary.com/myCloud/image/upload/v1371995958/other/c87hg9xfxrd4itiim3t0',
                    'etag' => 'e522f43cf89aa0afd03387c37e2b6e29',
                ],
                new RemoteResource(
                    remoteId: 'upload|image|other/c87hg9xfxrd4itiim3t0',
                    type: 'document',
                    url: 'https://res.cloudinary.com/myCloud/image/upload/other/c87hg9xfxrd4itiim3t0',
                    md5: 'e522f43cf89aa0afd03387c37e2b6e29',
                    name: 'c87hg9xfxrd4itiim3t0',
                    visibility: 'public',
                    folder: Folder::fromPath('other'),
                    size: 120253,
                    altText: null,
                    caption: null,
                    tags: [],
                    metadata: [
                        'signature' => 'f8645b000be7d717599affc89a068157e4748276',
                        'format' => 'pdf',
                        'created_at' => '2013-06-23T13:59:18Z',
                    ],
                ),
            ],
            [
                [
                    'public_id' => 'c87hg9xfxrd4itiim3t0',
                    'version' => 13711295958,
                    'signature' => 'f8645b000be7d717599affc89a068157e4748276',
                    'width' => 864,
                    'height' => 576,
                    'format' => 'mp4',
                    'resource_type' => 'video',
                    'created_at' => '2013-06-23T13:59:18Z',
                    'bytes' => 120253,
                    'url' => 'http://res.cloudinary.com/myCloud/video/upload/v1371995958/c87hg9xfxrd4itiim3t0.mp4',
                    'tags' => ['tag1', 'tag2'],
                    'overwritten' => 'false',
                    'context' => [
                        'alt' => 'alt text',
                        'alt_text' => 'alt text',
                        'caption' => 'caption text',
                    ],
                    'variations' => [
                        'variation1',
                        'variation2',
                    ],
                ],
                new RemoteResource(
                    remoteId: 'upload|video|c87hg9xfxrd4itiim3t0',
                    type: 'video',
                    url: 'https://res.cloudinary.com/myCloud/video/upload/c87hg9xfxrd4itiim3t0',
                    md5: 'a522f23sf81aa0afd03387c37e2b6eax',
                    name: 'c87hg9xfxrd4itiim3t0',
                    version: '13711295958',
                    size: 120253,
                    altText: 'alt text',
                    caption: 'caption text',
                    tags: ['tag1', 'tag2'],
                    metadata: [
                        'signature' => 'f8645b000be7d717599affc89a068157e4748276',
                        'width' => 864,
                        'height' => 576,
                        'format' => 'mp4',
                        'created_at' => '2013-06-23T13:59:18Z',
                        'overwritten' => 'false',
                    ],
                ),
            ],
            [
                [
                    'public_id' => 'c87hg9xfxrd4itiim3t0',
                    'version' => 1371995958,
                    'signature' => 'f8645b000be7d717599affc89a068157e4748276',
                    'width' => 864,
                    'height' => 576,
                    'format' => 'mp4',
                    'resource_type' => 'video',
                    'created_at' => '2013-06-23T13:59:18Z',
                    'bytes' => 120253,
                    'type' => 'authenticated',
                    'url' => 'http://res.cloudinary.com/myCloud/video/authenticated/v1371995958/c87hg9xfxrd4itiim3t0.mp4',
                    'secure_url' => 'https://res.cloudinary.com/myCloud/video/authenticated/v1371995958/c87hg9xfxrd4itiim3t0.mp4',
                    'tags' => ['tag1', 'tag2'],
                    'overwritten' => 'false',
                    'context' => [],
                    'variations' => [
                        'variation1',
                        'variation2',
                    ],
                ],
                new RemoteResource(
                    remoteId: 'authenticated|video|c87hg9xfxrd4itiim3t0',
                    type: 'video',
                    url: 'https://res.cloudinary.com/myCloud/video/authenticated/c87hg9xfxrd4itiim3t0',
                    md5: 'a522f23sf81aa0afd03387c37e2b6eax',
                    name: 'c87hg9xfxrd4itiim3t0',
                    version: '1371995958',
                    visibility: 'protected',
                    size: 120253,
                    tags: ['tag1', 'tag2'],
                    metadata: [
                        'signature' => 'f8645b000be7d717599affc89a068157e4748276',
                        'width' => 864,
                        'height' => 576,
                        'format' => 'mp4',
                        'created_at' => '2013-06-23T13:59:18Z',
                        'overwritten' => 'false',
                    ],
                ),
            ],
            [
                [
                    'public_id' => 'c87hg9xfxrd4itiim3t0',
                    'version' => 1371995958,
                    'signature' => 'f8645b000be7d717599affc89a068157e4748276',
                    'format' => 'mp3',
                    'resource_type' => 'video',
                    'created_at' => '2013-06-23T13:59:18Z',
                    'bytes' => 12025,
                    'type' => 'authenticated',
                    'secure_url' => 'https://res.cloudinary.com/myCloud/authenticated/video/v1371995958/c87hg9xfxrd4itiim3t0.mp3',
                    'etag' => 'e522f43cf89aa0afd03387c37e2b6e29',
                    'overwritten' => 'false',
                    'context' => [
                        'test' => 'test',
                    ],
                    'variations' => [
                        'variation1',
                        'variation2',
                    ],
                ],
                new RemoteResource(
                    remoteId: 'authenticated|video|c87hg9xfxrd4itiim3t0',
                    type: 'audio',
                    url: 'https://res.cloudinary.com/myCloud/video/authenticated/c87hg9xfxrd4itiim3t0',
                    md5: 'e522f43cf89aa0afd03387c37e2b6e29',
                    name: 'c87hg9xfxrd4itiim3t0',
                    version: '1371995958',
                    visibility: 'protected',
                    size: 12025,
                    tags: [],
                    metadata: [
                        'signature' => 'f8645b000be7d717599affc89a068157e4748276',
                        'format' => 'mp3',
                        'created_at' => '2013-06-23T13:59:18Z',
                        'overwritten' => 'false',
                    ],
                    context: [
                        'test' => 'test',
                    ],
                ),
            ],
            [
                [
                    'public_id' => 'media/raw/new/c87hg9xfxrd4itiim3t0',
                    'version' => 1371995958,
                    'signature' => 'f8645b000be7d717599affc89a068157e4748276',
                    'format' => 'zip',
                    'resource_type' => 'raw',
                    'created_at' => '2011-06-23T13:59:18Z',
                    'bytes' => 12025,
                    'type' => 'test',
                    'secure_url' => 'https://res.cloudinary.com/myCloud/v1371995958/raw/media/raw/new/c87hg9xfxrd4itiim3t0',
                    'etag' => 'e522f43cf89aa0afd03387c38e2b6e29',
                    'context' => [
                        'test' => 'test',
                        'custom' => [
                            'test2' => 'test2',
                        ],
                    ],
                ],
                new RemoteResource(
                    remoteId: 'test|raw|media/raw/new/c87hg9xfxrd4itiim3t0',
                    type: 'other',
                    url: 'https://res.cloudinary.com/myCloud/raw/test/media/raw/new/c87hg9xfxrd4itiim3t0',
                    md5: 'e522f43cf89aa0afd03387c38e2b6e29',
                    name: 'c87hg9xfxrd4itiim3t0',
                    version: '1371995958',
                    visibility: 'public',
                    folder: Folder::fromPath('media/raw/new'),
                    size: 12025,
                    tags: [],
                    metadata: [
                        'signature' => 'f8645b000be7d717599affc89a068157e4748276',
                        'format' => 'zip',
                        'created_at' => '2011-06-23T13:59:18Z',
                    ],
                    context: [
                        'test' => 'test',
                        'test2' => 'test2',
                    ],
                ),
            ],
        ];
    }
}
