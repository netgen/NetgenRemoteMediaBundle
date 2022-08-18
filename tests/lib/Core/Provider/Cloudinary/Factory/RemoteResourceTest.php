<?php

declare(strict_types=1);

namespace Netgen\RemoteMedia\Tests\Core\Provider\Cloudinary\Factory;

use Cloudinary\Api\Response as CloudinaryApiResponse;
use Netgen\RemoteMedia\API\Values\RemoteResource;
use Netgen\RemoteMedia\Core\Provider\Cloudinary\Factory\RemoteResource as RemoteResourceFactory;
use Netgen\RemoteMedia\Core\Provider\Cloudinary\Converter\ResourceType as ResourceTypeConverter;
use Netgen\RemoteMedia\Exception\Factory\InvalidDataException;
use PHPUnit\Framework\TestCase;

use function json_encode;

final class RemoteResourceTest extends TestCase
{
    protected RemoteResourceFactory $remoteResourceFactory;

    protected function setUp(): void
    {
        $this->remoteResourceFactory = new RemoteResourceFactory(
            new ResourceTypeConverter(),
        );
    }

    /**
     * @covers \Netgen\RemoteMedia\Core\Provider\Cloudinary\Factory\RemoteResource::create
     * @dataProvider createDataProvider
     */
    public function testCreateImage(CloudinaryApiResponse $cloudinaryResponse, RemoteResource $expectedResource): void
    {
        $resource = $this->remoteResourceFactory->create($cloudinaryResponse);

        self::assertNull($resource->getId());

        self::assertSame(
            $expectedResource->getRemoteId(),
            $resource->getRemoteId(),
        );

        self::assertSame(
            $expectedResource->getType(),
            $resource->getType(),
        );

        self::assertSame(
            $expectedResource->getUrl(),
            $resource->getUrl(),
        );

        self::assertSame(
            $expectedResource->getSize(),
            $resource->getSize(),
        );

        self::assertSame(
            $expectedResource->getAltText(),
            $resource->getAltText(),
        );

        self::assertSame(
            $expectedResource->getCaption(),
            $resource->getCaption(),
        );

        self::assertSame(
            $expectedResource->getTags(),
            $resource->getTags(),
        );

        self::assertSame(
            $expectedResource->getMetaData(),
            $resource->getMetaData(),
        );
    }

    /**
     * @covers \Netgen\RemoteMedia\Core\Provider\Cloudinary\Factory\RemoteResource::create
     */
    public function testCreateInvalidData(): void
    {
        self::expectException(InvalidDataException::class);
        self::expectExceptionMessage('CloudinaryRemoteResourceFactory requires "Cloudinary\Api\Response" as data, "string" provided.');

        $this->remoteResourceFactory->create('test');
    }

    /**
     * @covers \Netgen\RemoteMedia\Core\Provider\Cloudinary\Factory\RemoteResource::create
     */
    public function testCreateMissingPublicId(): void
    {
        self::expectException(InvalidDataException::class);
        self::expectExceptionMessage('Missing required "public_id" property!');

        $this->remoteResourceFactory->create(
            $this->createCloudinaryApiResponseFromArray(['test' => 'test']),
        );
    }

    public function createDataProvider(): array
    {
        return [
            [
                $this->createCloudinaryApiResponseFromArray([
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
                ]),
                new RemoteResource([
                    'remoteId' => 'upload|image|c87hg9xfxrd4itiim3t0',
                    'type' => 'image',
                    'url' => 'https://res.cloudinary.com/demo/image/upload/v1371995958/c87hg9xfxrd4itiim3t0.jpg',
                    'size' => 120253,
                    'altText' => 'alt text',
                    'caption' => 'caption text',
                    'tags' => ['tag1'],
                    'metaData' => [
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
                $this->createCloudinaryApiResponseFromArray([
                    'public_id' => 'c87hg9xfxrd4itiim3t0',
                    'version' => '1371995958',
                    'signature' => 'f8645b000be7d717599affc89a068157e4748276',
                    'format' => 'pdf',
                    'resource_type' => 'image',
                    'created_at' => '2013-06-23T13:59:18Z',
                    'bytes' => 120253,
                    'type' => 'upload',
                    'url' => 'http://res.cloudinary.com/demo/image/upload/v1371995958/c87hg9xfxrd4itiim3t0',
                    'secure_url' => 'https://res.cloudinary.com/demo/image/upload/v1371995958/c87hg9xfxrd4itiim3t0',
                ]),
                new RemoteResource([
                    'remoteId' => 'upload|image|c87hg9xfxrd4itiim3t0',
                    'type' => 'document',
                    'url' => 'https://res.cloudinary.com/demo/image/upload/v1371995958/c87hg9xfxrd4itiim3t0',
                    'size' => 120253,
                    'altText' => null,
                    'caption' => null,
                    'tags' => [],
                    'metaData' => [
                        'version' => '1371995958',
                        'signature' => 'f8645b000be7d717599affc89a068157e4748276',
                        'format' => 'pdf',
                        'created_at' => '2013-06-23T13:59:18Z',
                    ],
                ]),
            ],
            [
                $this->createCloudinaryApiResponseFromArray([
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
                ]),
                new RemoteResource([
                    'remoteId' => 'upload|video|c87hg9xfxrd4itiim3t0',
                    'type' => 'video',
                    'url' => 'http://res.cloudinary.com/demo/video/upload/v1371995958/c87hg9xfxrd4itiim3t0.mp4',
                    'size' => 120253,
                    'altText' => 'alt text',
                    'tags' => ['tag1', 'tag2'],
                    'metaData' => [
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
                $this->createCloudinaryApiResponseFromArray([
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
                ]),
                new RemoteResource([
                    'remoteId' => 'upload|video|c87hg9xfxrd4itiim3t0',
                    'type' => 'video',
                    'url' => 'http://res.cloudinary.com/demo/video/upload/v1371995958/c87hg9xfxrd4itiim3t0.mp4',
                    'size' => 120253,
                    'tags' => ['tag1', 'tag2'],
                    'metaData' => [
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
                $this->createCloudinaryApiResponseFromArray([
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
                ]),
                new RemoteResource([
                    'remoteId' => 'private|video|c87hg9xfxrd4itiim3t0',
                    'type' => 'audio',
                    'url' => 'https://res.cloudinary.com/demo/video/upload/v1371995958/c87hg9xfxrd4itiim3t0.mp3',
                    'size' => 12025,
                    'tags' => [],
                    'metaData' => [
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
                $this->createCloudinaryApiResponseFromArray([
                    'public_id' => 'c87hg9xfxrd4itiim3t0',
                    'version' => '1371995958',
                    'signature' => 'f8645b000be7d717599affc89a068157e4748276',
                    'format' => 'zip',
                    'resource_type' => 'raw',
                    'created_at' => '2011-06-23T13:59:18Z',
                    'bytes' => 12025,
                    'type' => 'private',
                    'secure_url' => 'https://res.cloudinary.com/demo/video/upload/v1371995958/c87hg9xfxrd4itiim3t0.zip',
                ]),
                new RemoteResource([
                    'remoteId' => 'private|raw|c87hg9xfxrd4itiim3t0',
                    'type' => 'other',
                    'url' => 'https://res.cloudinary.com/demo/video/upload/v1371995958/c87hg9xfxrd4itiim3t0.zip',
                    'size' => 12025,
                    'tags' => [],
                    'metaData' => [
                        'version' => '1371995958',
                        'signature' => 'f8645b000be7d717599affc89a068157e4748276',
                        'format' => 'zip',
                        'created_at' => '2011-06-23T13:59:18Z',
                    ],
                ]),
            ],
        ];
    }

    private function createCloudinaryApiResponseFromArray(array $data): CloudinaryApiResponse
    {
        return new CloudinaryApiResponse((object) [
            'body' => json_encode($data),
            'responseCode' => 200,
            'headers' => [
                'X-FeatureRateLimit-Reset' => 'test',
                'X-FeatureRateLimit-Limit' => 'test',
                'X-FeatureRateLimit-Remaining' => 'test',
            ],
        ]);
    }
}
