<?php

declare(strict_types=1);

namespace Netgen\RemoteMedia\Tests\API\Values;

use Netgen\RemoteMedia\API\Values\RemoteResource;
use PHPUnit\Framework\TestCase;

final class RemoteResourceTest extends TestCase
{
    /**
     * @covers \Netgen\RemoteMedia\API\Values\RemoteResource::__construct
     * @covers \Netgen\RemoteMedia\API\Values\RemoteResource::getId
     * @covers \Netgen\RemoteMedia\API\Values\RemoteResource::getMetadata
     * @covers \Netgen\RemoteMedia\API\Values\RemoteResource::getMetadataProperty
     * @covers \Netgen\RemoteMedia\API\Values\RemoteResource::getRemoteId
     * @covers \Netgen\RemoteMedia\API\Values\RemoteResource::getSize
     * @covers \Netgen\RemoteMedia\API\Values\RemoteResource::getTags
     * @covers \Netgen\RemoteMedia\API\Values\RemoteResource::getType
     * @covers \Netgen\RemoteMedia\API\Values\RemoteResource::getUrl
     * @covers \Netgen\RemoteMedia\API\Values\RemoteResource::hasMetadataProperty
     * @covers \Netgen\RemoteMedia\API\Values\RemoteResource::hasTag
     */
    public function testConstruct(): void
    {
        $resource = new RemoteResource([
            'id' => 56,
            'remoteId' => 'upload|image|c87hg9xfxrd4itiim3t0',
            'type' => 'image',
            'url' => 'https://res.cloudinary.com/demo/image/upload/v1371995958/c87hg9xfxrd4itiim3t0.jpg',
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
        ]);

        self::assertSame(
            56,
            $resource->getId(),
        );

        self::assertSame(
            'upload|image|c87hg9xfxrd4itiim3t0',
            $resource->getRemoteId(),
        );

        self::assertSame(
            'image',
            $resource->getType(),
        );

        self::assertSame(
            'https://res.cloudinary.com/demo/image/upload/v1371995958/c87hg9xfxrd4itiim3t0.jpg',
            $resource->getUrl(),
        );

        self::assertSame(
            120253,
            $resource->getSize(),
        );

        self::assertSame(
            'alt text',
            $resource->getAltText(),
        );

        self::assertSame(
            'caption text',
            $resource->getCaption(),
        );

        self::assertSame(
            ['tag1'],
            $resource->getTags(),
        );

        self::assertTrue($resource->hasTag('tag1'));
        self::assertFalse($resource->hasTag('tag2'));

        self::assertSame(
            [
                'version' => '1371995958',
                'signature' => 'f8645b000be7d717599affc89a068157e4748276',
                'width' => 864,
                'height' => 576,
                'format' => 'jpg',
                'created_at' => '2013-06-23T13:59:18Z',
                'etag' => 'test_tag',
                'overwritten' => 'true',
            ],
            $resource->getMetaData(),
        );

        self::assertSame(
            864,
            $resource->getMetaDataProperty('width'),
        );

        self::assertTrue($resource->hasMetaDataProperty('version'));
        self::assertFalse($resource->hasMetaDataProperty('non_existing_parameter'));
        self::assertNull($resource->getMetaDataProperty('non_existing_parameter'));
    }

    /**
     * @covers \Netgen\RemoteMedia\API\Values\RemoteResource::addTag
     * @covers \Netgen\RemoteMedia\API\Values\RemoteResource::getTags
     * @covers \Netgen\RemoteMedia\API\Values\RemoteResource::hasTag
     */
    public function testAddTag(): void
    {
        $resource = new RemoteResource();

        self::assertEmpty($resource->getTags());
        self::assertFalse($resource->hasTag('tag1'));

        $resource->addTag('tag1');

        self::assertSame(
            ['tag1'],
            $resource->getTags(),
        );

        self::assertTrue($resource->hasTag('tag1'));
    }

    /**
     * @covers \Netgen\RemoteMedia\API\Values\RemoteResource::getTags
     * @covers \Netgen\RemoteMedia\API\Values\RemoteResource::hasTag
     * @covers \Netgen\RemoteMedia\API\Values\RemoteResource::removeTag
     */
    public function testRemoveTags(): void
    {
        $resource = new RemoteResource(['tags' => ['tag1']]);

        self::assertSame(
            ['tag1'],
            $resource->getTags(),
        );

        self::assertTrue($resource->hasTag('tag1'));

        $resource->removeTag('tag1');

        self::assertEmpty($resource->getTags());
        self::assertFalse($resource->hasTag('tag1'));
    }

    public function testLocations(): void
    {
        $resource = new RemoteResource();

        self::assertEmpty($resource->locations);
    }
}
