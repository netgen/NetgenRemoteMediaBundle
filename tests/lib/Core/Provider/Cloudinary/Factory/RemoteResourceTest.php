<?php

declare(strict_types=1);

namespace Netgen\RemoteMedia\Tests\Core\Provider\Cloudinary\Factory;

use Netgen\RemoteMedia\API\Values\Folder;
use Netgen\RemoteMedia\API\Values\RemoteResource;
use Netgen\RemoteMedia\Core\Provider\Cloudinary\Converter\ResourceType as ResourceTypeConverter;
use Netgen\RemoteMedia\Core\Provider\Cloudinary\Factory\RemoteResource as RemoteResourceFactory;
use Netgen\RemoteMedia\Exception\Factory\InvalidDataException;
use Netgen\RemoteMedia\Tests\AbstractTest;

final class RemoteResourceTest extends AbstractTest
{
    protected RemoteResourceFactory $remoteResourceFactory;

    protected function setUp(): void
    {
        $this->remoteResourceFactory = new RemoteResourceFactory(
            new ResourceTypeConverter(),
        );
    }

    /**
     * @covers \Netgen\RemoteMedia\Core\Provider\Cloudinary\Factory\RemoteResource::__construct
     * @covers \Netgen\RemoteMedia\Core\Provider\Cloudinary\Factory\RemoteResource::create
     * @covers \Netgen\RemoteMedia\Core\Provider\Cloudinary\Factory\RemoteResource::resolveAltText
     * @covers \Netgen\RemoteMedia\Core\Provider\Cloudinary\Factory\RemoteResource::resolveMetaData
     * @covers \Netgen\RemoteMedia\Core\Provider\Cloudinary\Factory\RemoteResource::resolveResourceType
     * @covers \Netgen\RemoteMedia\Core\Provider\Cloudinary\Factory\RemoteResource::validateData
     * @dataProvider createDataProvider
     */
    public function testCreateImage(array $cloudinaryResponse, RemoteResource $expectedResource): void
    {
        $resource = $this->remoteResourceFactory->create($cloudinaryResponse);

        self::assertRemoteResourceSame(
            $expectedResource,
            $resource,
        );
    }

    /**
     * @covers \Netgen\RemoteMedia\Core\Provider\Cloudinary\Factory\RemoteResource::create
     * @covers \Netgen\RemoteMedia\Core\Provider\Cloudinary\Factory\RemoteResource::validateData
     */
    public function testCreateMissingPublicId(): void
    {
        self::expectException(InvalidDataException::class);
        self::expectExceptionMessage('Missing required "public_id" property!');

        $this->remoteResourceFactory->create(['test' => 'test']);
    }

    public function createDataProvider(): array
    {
        return [
            [
                [
                    'public_id' => 'c87hg9xfxrd4itiim3t0',
                    'version' => '1371995958',
                    'signature' => 'f8645b000be7d717599affc89a068157e4748276',
                    'width' => 864,
                    'height' => 576,
                    'format' => 'jpg',
                    'resource_type' => 'image',
                    'created_at' => '2013-06-23T13:59:18Z',
                    'bytes' => 120253,
                    'type' => 'upload',
                    'url' => 'http://res.cloudinary.com/demo/image/upload/v1371995958/c87hg9xfxrd4itiim3t0.jpg',
                    'secure_url' => 'https://res.cloudinary.com/demo/image/upload/v1371995958/c87hg9xfxrd4itiim3t0.jpg',
                    'etag' => 'test_tag',
                    'tags' => ['tag1'],
                    'overwritten' => 'true',
                    'context' => [
                        'custom' => [
                            'alt' => 'alt text',
                            'alt_text' => 'alt text',
                            'caption' => 'caption text',
                        ],
                    ],
                    'variations' => [
                        'variation1',
                        'variation2',
                    ],
                ],
                new RemoteResource([
                    'remoteId' => 'upload|image|c87hg9xfxrd4itiim3t0',
                    'type' => 'image',
                    'url' => 'https://res.cloudinary.com/demo/image/upload/v1371995958/c87hg9xfxrd4itiim3t0.jpg',
                    'name' => 'c87hg9xfxrd4itiim3t0',
                    'size' => 120253,
                    'altText' => 'alt text',
                    'caption' => 'caption text',
                    'tags' => ['tag1'],
                    'metadata' => [
                        'version' => '1371995958',
                        'signature' => 'f8645b000be7d717599affc89a068157e4748276',
                        'width' => 864,
                        'height' => 576,
                        'format' => 'jpg',
                        'created_at' => '2013-06-23T13:59:18Z',
                        'etag' => 'test_tag',
                        'overwritten' => 'true',
                    ],
                ]),
            ],
            [
                [
                    'public_id' => 'other/c87hg9xfxrd4itiim3t0',
                    'version' => '1371995958',
                    'signature' => 'f8645b000be7d717599affc89a068157e4748276',
                    'format' => 'pdf',
                    'resource_type' => 'image',
                    'created_at' => '2013-06-23T13:59:18Z',
                    'bytes' => 120253,
                    'type' => 'upload',
                    'url' => 'http://res.cloudinary.com/demo/image/upload/v1371995958/other/c87hg9xfxrd4itiim3t0',
                    'secure_url' => 'https://res.cloudinary.com/demo/image/upload/v1371995958/other/c87hg9xfxrd4itiim3t0',
                ],
                new RemoteResource([
                    'remoteId' => 'upload|image|other/c87hg9xfxrd4itiim3t0',
                    'type' => 'document',
                    'url' => 'https://res.cloudinary.com/demo/image/upload/v1371995958/other/c87hg9xfxrd4itiim3t0',
                    'name' => 'c87hg9xfxrd4itiim3t0',
                    'folder' => Folder::fromPath('other'),
                    'size' => 120253,
                    'altText' => null,
                    'caption' => null,
                    'tags' => [],
                    'metadata' => [
                        'version' => '1371995958',
                        'signature' => 'f8645b000be7d717599affc89a068157e4748276',
                        'format' => 'pdf',
                        'created_at' => '2013-06-23T13:59:18Z',
                    ],
                ]),
            ],
            [
                [
                    'public_id' => 'c87hg9xfxrd4itiim3t0',
                    'version' => '1371995958',
                    'signature' => 'f8645b000be7d717599affc89a068157e4748276',
                    'width' => 864,
                    'height' => 576,
                    'format' => 'mp4',
                    'resource_type' => 'video',
                    'created_at' => '2013-06-23T13:59:18Z',
                    'bytes' => 120253,
                    'type' => 'upload',
                    'url' => 'http://res.cloudinary.com/demo/video/upload/v1371995958/c87hg9xfxrd4itiim3t0.mp4',
                    'etag' => 'test_tag',
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
                new RemoteResource([
                    'remoteId' => 'upload|video|c87hg9xfxrd4itiim3t0',
                    'type' => 'video',
                    'url' => 'http://res.cloudinary.com/demo/video/upload/v1371995958/c87hg9xfxrd4itiim3t0.mp4',
                    'name' => 'c87hg9xfxrd4itiim3t0',
                    'size' => 120253,
                    'altText' => 'alt text',
                    'tags' => ['tag1', 'tag2'],
                    'metadata' => [
                        'version' => '1371995958',
                        'signature' => 'f8645b000be7d717599affc89a068157e4748276',
                        'width' => 864,
                        'height' => 576,
                        'format' => 'mp4',
                        'created_at' => '2013-06-23T13:59:18Z',
                        'etag' => 'test_tag',
                        'overwritten' => 'false',
                    ],
                ]),
            ],
            [
                [
                    'public_id' => 'c87hg9xfxrd4itiim3t0',
                    'version' => '1371995958',
                    'signature' => 'f8645b000be7d717599affc89a068157e4748276',
                    'width' => 864,
                    'height' => 576,
                    'format' => 'mp4',
                    'resource_type' => 'video',
                    'created_at' => '2013-06-23T13:59:18Z',
                    'bytes' => 120253,
                    'type' => 'upload',
                    'url' => 'http://res.cloudinary.com/demo/video/upload/v1371995958/c87hg9xfxrd4itiim3t0.mp4',
                    'etag' => 'test_tag',
                    'tags' => ['tag1', 'tag2'],
                    'overwritten' => 'false',
                    'context' => [],
                    'variations' => [
                        'variation1',
                        'variation2',
                    ],
                ],
                new RemoteResource([
                    'remoteId' => 'upload|video|c87hg9xfxrd4itiim3t0',
                    'type' => 'video',
                    'url' => 'http://res.cloudinary.com/demo/video/upload/v1371995958/c87hg9xfxrd4itiim3t0.mp4',
                    'name' => 'c87hg9xfxrd4itiim3t0',
                    'size' => 120253,
                    'tags' => ['tag1', 'tag2'],
                    'metadata' => [
                        'version' => '1371995958',
                        'signature' => 'f8645b000be7d717599affc89a068157e4748276',
                        'width' => 864,
                        'height' => 576,
                        'format' => 'mp4',
                        'created_at' => '2013-06-23T13:59:18Z',
                        'etag' => 'test_tag',
                        'overwritten' => 'false',
                    ],
                ]),
            ],
            [
                [
                    'public_id' => 'c87hg9xfxrd4itiim3t0',
                    'version' => '1371995958',
                    'signature' => 'f8645b000be7d717599affc89a068157e4748276',
                    'format' => 'mp3',
                    'resource_type' => 'video',
                    'created_at' => '2013-06-23T13:59:18Z',
                    'bytes' => 12025,
                    'type' => 'private',
                    'secure_url' => 'https://res.cloudinary.com/demo/video/upload/v1371995958/c87hg9xfxrd4itiim3t0.mp3',
                    'etag' => 'test_tag',
                    'overwritten' => 'false',
                    'context' => [],
                    'variations' => [
                        'variation1',
                        'variation2',
                    ],
                ],
                new RemoteResource([
                    'remoteId' => 'private|video|c87hg9xfxrd4itiim3t0',
                    'type' => 'audio',
                    'url' => 'https://res.cloudinary.com/demo/video/upload/v1371995958/c87hg9xfxrd4itiim3t0.mp3',
                    'name' => 'c87hg9xfxrd4itiim3t0',
                    'size' => 12025,
                    'tags' => [],
                    'metadata' => [
                        'version' => '1371995958',
                        'signature' => 'f8645b000be7d717599affc89a068157e4748276',
                        'format' => 'mp3',
                        'created_at' => '2013-06-23T13:59:18Z',
                        'etag' => 'test_tag',
                        'overwritten' => 'false',
                    ],
                ]),
            ],
            [
                [
                    'public_id' => 'media/raw/new/c87hg9xfxrd4itiim3t0',
                    'version' => '1371995958',
                    'signature' => 'f8645b000be7d717599affc89a068157e4748276',
                    'format' => 'zip',
                    'resource_type' => 'raw',
                    'created_at' => '2011-06-23T13:59:18Z',
                    'bytes' => 12025,
                    'type' => 'private',
                    'secure_url' => 'https://res.cloudinary.com/demo/video/upload/v1371995958/media/raw/new/c87hg9xfxrd4itiim3t0.zip',
                ],
                new RemoteResource([
                    'remoteId' => 'private|raw|media/raw/new/c87hg9xfxrd4itiim3t0',
                    'type' => 'other',
                    'url' => 'https://res.cloudinary.com/demo/video/upload/v1371995958/media/raw/new/c87hg9xfxrd4itiim3t0.zip',
                    'name' => 'c87hg9xfxrd4itiim3t0',
                    'folder' => Folder::fromPath('media/raw/new'),
                    'size' => 12025,
                    'tags' => [],
                    'metadata' => [
                        'version' => '1371995958',
                        'signature' => 'f8645b000be7d717599affc89a068157e4748276',
                        'format' => 'zip',
                        'created_at' => '2011-06-23T13:59:18Z',
                    ],
                ]),
            ],
        ];
    }
}
