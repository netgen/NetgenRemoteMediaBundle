<?php

declare(strict_types=1);

namespace Netgen\RemoteMedia\Tests\API\Values;

use Netgen\RemoteMedia\API\Values\RemoteResource;
use PHPUnit\Framework\TestCase;
use function json_encode;

final class RemoteResourceTest extends TestCase
{
    public const EXAMPLE_PARAMETERS = [
        'resourceId' => 'c87hg9xfxrd4itiim3t0',
        'resourceType' => 'image',
        'mediaType' => 'image',
        'type' => 'upload',
        'url' => 'http://res.cloudinary.com/demo/image/upload/v1371995958/c87hg9xfxrd4itiim3t0.jpg',
        'secure_url' => 'https://res.cloudinary.com/demo/image/upload/v1371995958/c87hg9xfxrd4itiim3t0.jpg',
        'size' => 120253,
        'variations' => [
            'variation1',
            'variation2',
        ],
        'metaData' => [
            'version' => '1371995958',
            'width' => 864,
            'height' => 576,
            'format' => 'jpg',
            'created' => '2013-06-23T13:59:18Z',
            'tags' => ['tag1'],
            'signature' => 'f8645b000be7d717599affc89a068157e4748276',
            'etag' => 'test_tag',
            'overwritten' => 'true',
            'alt_text' => 'alt text',
            'caption' => 'caption text',
        ],
    ];

    public const EMPTY_PARAMETERS = [
        'resourceId' => null,
        'resourceType' => null,
        'mediaType' => 'image',
        'type' => 'upload',
        'url' => null,
        'secure_url' => null,
        'size' => 0,
        'variations' => [],
        'metaData' => [
            'version' => '',
            'width' => '',
            'height' => '',
            'created' => '',
            'format' => '',
            'tags' => [],
            'signature' => '',
            'etag' => '',
            'overwritten' => '',
            'alt_text' => '',
            'caption' => '',
        ],
    ];

    public const EXAMPLE_CLOUDINARY_RESPONSE = [
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
    ];

    /**
     * @covers \Netgen\RemoteMedia\API\Values\RemoteResource::__construct
     * @covers \Netgen\RemoteMedia\API\Values\RemoteResource::__toString
     */
    public function testConstructionWithParameters(): void
    {
        $resource = new RemoteResource(self::EXAMPLE_PARAMETERS);

        self::assertSame(json_encode(self::EXAMPLE_PARAMETERS), (string) $resource);
    }

    /**
     * @covers \Netgen\RemoteMedia\API\Values\RemoteResource::__construct
     * @covers \Netgen\RemoteMedia\API\Values\RemoteResource::__toString
     */
    public function testConstructionWithoutParameters(): void
    {
        $resource = new RemoteResource();

        self::assertSame(json_encode(self::EMPTY_PARAMETERS), (string) $resource);
    }

    /**
     * @covers \Netgen\RemoteMedia\API\Values\RemoteResource::__toString
     * @covers \Netgen\RemoteMedia\API\Values\RemoteResource::createFromCloudinaryResponse
     */
    public function testImageConstructionFromCloudinaryResponse(): void
    {
        $resource = RemoteResource::createFromCloudinaryResponse(self::EXAMPLE_CLOUDINARY_RESPONSE);

        self::assertSame(json_encode(self::EXAMPLE_PARAMETERS), (string) $resource);
    }

    /**
     * @covers \Netgen\RemoteMedia\API\Values\RemoteResource::__toString
     * @covers \Netgen\RemoteMedia\API\Values\RemoteResource::createFromCloudinaryResponse
     */
    public function testVideoConstructionFromCloudinaryResponse(): void
    {
        $exampleCloudinaryResponse = self::EXAMPLE_CLOUDINARY_RESPONSE;
        $exampleCloudinaryResponse['resource_type'] = 'video';

        $resource = RemoteResource::createFromCloudinaryResponse($exampleCloudinaryResponse);

        $exampleParameters = self::EXAMPLE_PARAMETERS;
        $exampleParameters['mediaType'] = 'video';
        $exampleParameters['resourceType'] = 'video';
        $exampleParameters['type'] = 'upload';

        self::assertSame(json_encode($exampleParameters), (string) $resource);
    }

    /**
     * @covers \Netgen\RemoteMedia\API\Values\RemoteResource::__toString
     * @covers \Netgen\RemoteMedia\API\Values\RemoteResource::createFromCloudinaryResponse
     */
    public function testPdfConstructionFromCloudinaryResponse(): void
    {
        $exampleCloudinaryResponse = self::EXAMPLE_CLOUDINARY_RESPONSE;
        $exampleCloudinaryResponse['resource_type'] = 'pdf';

        $resource = RemoteResource::createFromCloudinaryResponse($exampleCloudinaryResponse);

        $exampleParameters = self::EXAMPLE_PARAMETERS;
        $exampleParameters['mediaType'] = 'other';
        $exampleParameters['resourceType'] = 'pdf';
        $exampleParameters['type'] = 'upload';

        self::assertSame(json_encode($exampleParameters), (string) $resource);
    }
}
