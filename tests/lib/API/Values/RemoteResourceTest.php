<?php

declare(strict_types=1);

namespace Netgen\RemoteMedia\Tests\API\Values;

use Netgen\RemoteMedia\API\Values\Folder;
use Netgen\RemoteMedia\API\Values\RemoteResource;
use Netgen\RemoteMedia\Tests\AbstractTest;

final class RemoteResourceTest extends AbstractTest
{
    /**
     * @covers \Netgen\RemoteMedia\API\Values\RemoteResource::__construct
     * @covers \Netgen\RemoteMedia\API\Values\RemoteResource::getAltText
     * @covers \Netgen\RemoteMedia\API\Values\RemoteResource::getCaption
     * @covers \Netgen\RemoteMedia\API\Values\RemoteResource::getContext
     * @covers \Netgen\RemoteMedia\API\Values\RemoteResource::getContextProperty
     * @covers \Netgen\RemoteMedia\API\Values\RemoteResource::getFolder
     * @covers \Netgen\RemoteMedia\API\Values\RemoteResource::getId
     * @covers \Netgen\RemoteMedia\API\Values\RemoteResource::getMd5
     * @covers \Netgen\RemoteMedia\API\Values\RemoteResource::getMetadata
     * @covers \Netgen\RemoteMedia\API\Values\RemoteResource::getMetadataProperty
     * @covers \Netgen\RemoteMedia\API\Values\RemoteResource::getName
     * @covers \Netgen\RemoteMedia\API\Values\RemoteResource::getRemoteId
     * @covers \Netgen\RemoteMedia\API\Values\RemoteResource::getSize
     * @covers \Netgen\RemoteMedia\API\Values\RemoteResource::getTags
     * @covers \Netgen\RemoteMedia\API\Values\RemoteResource::getType
     * @covers \Netgen\RemoteMedia\API\Values\RemoteResource::getUrl
     * @covers \Netgen\RemoteMedia\API\Values\RemoteResource::getVersion
     * @covers \Netgen\RemoteMedia\API\Values\RemoteResource::getVisibility
     * @covers \Netgen\RemoteMedia\API\Values\RemoteResource::hasContextProperty
     * @covers \Netgen\RemoteMedia\API\Values\RemoteResource::hasMetadataProperty
     * @covers \Netgen\RemoteMedia\API\Values\RemoteResource::hasTag
     * @covers \Netgen\RemoteMedia\API\Values\RemoteResource::isPrivate
     * @covers \Netgen\RemoteMedia\API\Values\RemoteResource::isProtected
     * @covers \Netgen\RemoteMedia\API\Values\RemoteResource::isPublic
     */
    public function testConstruct(): void
    {
        $resource = new RemoteResource([
            'id' => 56,
            'remoteId' => 'upload|image|media/c87hg9xfxrd4itiim3t0',
            'type' => 'image',
            'url' => 'https://res.cloudinary.com/demo/image/upload/media/c87hg9xfxrd4itiim3t0.jpg',
            'name' => 'c87hg9xfxrd4itiim3t0',
            'version' => '1371995958',
            'folder' => Folder::fromPath('media'),
            'size' => 120253,
            'altText' => 'alt text',
            'caption' => 'caption text',
            'tags' => ['tag1'],
            'md5' => 'e522f43cf89aa0afd03387c37e2b6e29',
            'metadata' => [
                'signature' => 'f8645b000be7d717599affc89a068157e4748276',
                'width' => 864,
                'height' => 576,
                'format' => 'jpg',
                'created_at' => '2013-06-23T13:59:18Z',
                'etag' => 'test_tag',
                'overwritten' => 'true',
            ],
            'context' => [
                'original_filename' => 'c87hg9xfxrd4itiim3t0.jpg',
                'type' => 'shop_product',
            ],
            'test' => 'test',
        ]);

        self::assertSame(
            56,
            $resource->getId(),
        );

        self::assertSame(
            'upload|image|media/c87hg9xfxrd4itiim3t0',
            $resource->getRemoteId(),
        );

        self::assertSame(
            'image',
            $resource->getType(),
        );

        self::assertSame(
            'https://res.cloudinary.com/demo/image/upload/media/c87hg9xfxrd4itiim3t0.jpg',
            $resource->getUrl(),
        );

        self::assertSame(
            'c87hg9xfxrd4itiim3t0',
            $resource->getName(),
        );

        self::assertSame(
            '1371995958',
            $resource->getVersion(),
        );

        self::assertFolderSame(
            Folder::fromPath('media'),
            $resource->getFolder(),
        );

        self::assertTrue($resource->isPublic());
        self::assertFalse($resource->isPrivate());
        self::assertFalse($resource->isProtected());

        self::assertSame(
            'public',
            $resource->getVisibility(),
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
            'e522f43cf89aa0afd03387c37e2b6e29',
            $resource->getMd5(),
        );

        self::assertSame(
            [
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

        self::assertFalse($resource->hasMetaDataProperty('version'));
        self::assertFalse($resource->hasMetaDataProperty('non_existing_parameter'));
        self::assertNull($resource->getMetaDataProperty('non_existing_parameter'));

        self::assertSame(
            [
                'original_filename' => 'c87hg9xfxrd4itiim3t0.jpg',
                'type' => 'shop_product',
            ],
            $resource->getContext(),
        );

        self::assertSame(
            'c87hg9xfxrd4itiim3t0.jpg',
            $resource->getContextProperty('original_filename'),
        );

        self::assertTrue($resource->hasContextProperty('type'));
        self::assertFalse($resource->hasContextProperty('source'));
        self::assertNull($resource->getContextProperty('source'));
    }

    /**
     * @covers \Netgen\RemoteMedia\API\Values\RemoteResource::__construct
     * @covers \Netgen\RemoteMedia\API\Values\RemoteResource::setAltText
     * @covers \Netgen\RemoteMedia\API\Values\RemoteResource::setCaption
     * @covers \Netgen\RemoteMedia\API\Values\RemoteResource::setContext
     * @covers \Netgen\RemoteMedia\API\Values\RemoteResource::setFolder
     * @covers \Netgen\RemoteMedia\API\Values\RemoteResource::setMd5
     * @covers \Netgen\RemoteMedia\API\Values\RemoteResource::setMetadata
     * @covers \Netgen\RemoteMedia\API\Values\RemoteResource::setName
     * @covers \Netgen\RemoteMedia\API\Values\RemoteResource::setRemoteId
     * @covers \Netgen\RemoteMedia\API\Values\RemoteResource::setSize
     * @covers \Netgen\RemoteMedia\API\Values\RemoteResource::setTags
     * @covers \Netgen\RemoteMedia\API\Values\RemoteResource::setType
     * @covers \Netgen\RemoteMedia\API\Values\RemoteResource::setUrl
     * @covers \Netgen\RemoteMedia\API\Values\RemoteResource::setVersion
     * @covers \Netgen\RemoteMedia\API\Values\RemoteResource::setVisibility
     */
    public function testSetters(): void
    {
        $expected = new RemoteResource([
            'id' => 56,
            'remoteId' => 'upload|image|c87hg9xfxrd4itiim3t0',
            'type' => 'image',
            'url' => 'https://res.cloudinary.com/demo/image/upload/media/image/c87hg9xfxrd4itiim3t0.jpg',
            'name' => 'c87hg9xfxrd4itiim3t0',
            'version' => '1371995958',
            'folder' => Folder::fromPath('media/image'),
            'visibility' => 'protected',
            'size' => 120253,
            'altText' => 'alt text',
            'caption' => 'caption text',
            'tags' => ['tag1'],
            'md5' => 'e522f43cf89aa0afd03387c37e2b6e29',
            'metadata' => [
                'signature' => 'f8645b000be7d717599affc89a068157e4748276',
                'width' => 864,
                'height' => 576,
                'format' => 'jpg',
                'created_at' => '2013-06-23T13:59:18Z',
                'etag' => 'test_tag',
                'overwritten' => 'true',
            ],
            'context' => [
                'original_filename' => 'c87hg9xfxrd4itiim3t0.jpg',
                'type' => 'shop_product',
            ],
            'test' => 'test',
        ]);

        $resource = new RemoteResource(['id' => 56]);

        $resource
            ->setRemoteId('upload|image|c87hg9xfxrd4itiim3t0')
            ->setType('image')
            ->setUrl('https://res.cloudinary.com/demo/image/upload/media/image/c87hg9xfxrd4itiim3t0.jpg')
            ->setName('c87hg9xfxrd4itiim3t0')
            ->setVersion('1371995958')
            ->setFolder(Folder::fromPath('media/image'))
            ->setVisibility('protected')
            ->setSize(120253)
            ->setAltText('alt text')
            ->setCaption('caption text')
            ->setTags(['tag1'])
            ->setMd5('e522f43cf89aa0afd03387c37e2b6e29')
            ->setMetadata([
                'signature' => 'f8645b000be7d717599affc89a068157e4748276',
                'width' => 864,
                'height' => 576,
                'format' => 'jpg',
                'created_at' => '2013-06-23T13:59:18Z',
                'etag' => 'test_tag',
                'overwritten' => 'true',
            ])
            ->setContext([
                'original_filename' => 'c87hg9xfxrd4itiim3t0.jpg',
                'type' => 'shop_product',
            ]);

        self::assertRemoteResourceSame(
            $expected,
            $resource,
        );
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
     * @covers \Netgen\RemoteMedia\API\Values\RemoteResource::addTag
     * @covers \Netgen\RemoteMedia\API\Values\RemoteResource::getTags
     * @covers \Netgen\RemoteMedia\API\Values\RemoteResource::hasTag
     */
    public function testAddExistingTag(): void
    {
        $resource = new RemoteResource(['tags' => ['tag1']]);

        self::assertCount(1, $resource->getTags());
        self::assertTrue($resource->hasTag('tag1'));

        $resource->addTag('tag1');

        self::assertSame(
            ['tag1'],
            $resource->getTags(),
        );

        self::assertCount(1, $resource->getTags());
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
        $resource->removeTag('tag5');

        self::assertEmpty($resource->getTags());
        self::assertFalse($resource->hasTag('tag1'));
    }

    /**
     * @covers \Netgen\RemoteMedia\API\Values\RemoteResource::addContextProperty
     * @covers \Netgen\RemoteMedia\API\Values\RemoteResource::getContext
     * @covers \Netgen\RemoteMedia\API\Values\RemoteResource::getContextProperty
     * @covers \Netgen\RemoteMedia\API\Values\RemoteResource::hasContextProperty
     */
    public function testAddContextProperty(): void
    {
        $resource = new RemoteResource([
            'context' => [
                'source' => 'test_source',
            ],
        ]);

        self::assertSame(
            ['source' => 'test_source'],
            $resource->getContext(),
        );

        self::assertFalse($resource->hasContextProperty('type'));
        self::assertNull($resource->getContextProperty('type'));

        $resource->addContextProperty('type', 'product_image');

        self::assertSame(
            ['source' => 'test_source', 'type' => 'product_image'],
            $resource->getContext(),
        );

        self::assertTrue($resource->hasContextProperty('type'));

        self::assertSame(
            'product_image',
            $resource->getContextProperty('type'),
        );
    }

    /**
     * @covers \Netgen\RemoteMedia\API\Values\RemoteResource::getContext
     * @covers \Netgen\RemoteMedia\API\Values\RemoteResource::getContextProperty
     * @covers \Netgen\RemoteMedia\API\Values\RemoteResource::hasContextProperty
     * @covers \Netgen\RemoteMedia\API\Values\RemoteResource::removeContextProperty
     */
    public function testRemoveContextProperty(): void
    {
        $resource = new RemoteResource([
            'context' => [
                'source' => 'test_source',
            ],
        ]);

        self::assertSame(
            ['source' => 'test_source'],
            $resource->getContext(),
        );

        self::assertTrue($resource->hasContextProperty('source'));

        self::assertSame(
            'test_source',
            $resource->getContextProperty('source'),
        );

        $resource->removeContextProperty('source');

        self::assertEmpty($resource->getContext());
        self::assertFalse($resource->hasContextProperty('type'));
        self::assertNull($resource->getContextProperty('type'));
    }
}
