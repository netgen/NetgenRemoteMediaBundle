<?php

declare(strict_types=1);

namespace Netgen\RemoteMedia\Tests\API\Values;

use Netgen\RemoteMedia\API\Values\Folder;
use Netgen\RemoteMedia\API\Values\RemoteResource;
use Netgen\RemoteMedia\Tests\AbstractTestCase;
use PHPUnit\Framework\Attributes\CoversClass;

#[CoversClass(RemoteResource::class)]
final class RemoteResourceTest extends AbstractTestCase
{
    public function testConstruct(): void
    {
        $resource = new RemoteResource(
            remoteId: 'upload|image|media/c87hg9xfxrd4itiim3t0',
            type: 'image',
            url: 'https://res.cloudinary.com/demo/image/upload/media/c87hg9xfxrd4itiim3t0.jpg',
            md5: 'e522f43cf89aa0afd03387c37e2b6e29',
            id: 56,
            name: 'c87hg9xfxrd4itiim3t0',
            version: '1371995958',
            folder: Folder::fromPath('media'),
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
                'etag' => 'test_tag',
                'overwritten' => 'true',
            ],
            context: [
                'original_filename' => 'c87hg9xfxrd4itiim3t0.jpg',
                'type' => 'shop_product',
            ],
        );

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

    public function testSetters(): void
    {
        $expected = new RemoteResource(
            remoteId: 'upload|image|c87hg9xfxrd4itiim3t0',
            type: 'image',
            url: 'https://res.cloudinary.com/demo/image/upload/media/image/c87hg9xfxrd4itiim3t0.jpg',
            md5: 'e522f43cf89aa0afd03387c37e2b6e29',
            id: 56,
            name: 'c87hg9xfxrd4itiim3t0',
            version: '1371995958',
            visibility: 'protected',
            folder: Folder::fromPath('media/image'),
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
                'etag' => 'test_tag',
                'overwritten' => 'true',
            ],
            context: [
                'original_filename' => 'c87hg9xfxrd4itiim3t0.jpg',
                'type' => 'shop_product',
            ],
        );

        $resource = new RemoteResource(
            remoteId: 'upload|image|old_name',
            type: 'image',
            url: 'https://res.cloudinary.com/demo/image/upload/media/image/old_name.jpg',
            md5: '5f3409jg45jg5igj09g45',
            id: 56,
        );

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

    public function testAddTag(): void
    {
        $resource = new RemoteResource(
            remoteId: 'upload|image|old_name',
            type: 'image',
            url: 'https://res.cloudinary.com/demo/image/upload/media/image/old_name.jpg',
            md5: '5f3409jg45jg5igj09g45',
            id: 56,
        );

        self::assertEmpty($resource->getTags());
        self::assertFalse($resource->hasTag('tag1'));

        $resource->addTag('tag1');

        self::assertSame(
            ['tag1'],
            $resource->getTags(),
        );

        self::assertTrue($resource->hasTag('tag1'));
    }

    public function testAddExistingTag(): void
    {
        $resource = new RemoteResource(
            remoteId: 'upload|image|old_name',
            type: 'image',
            url: 'https://res.cloudinary.com/demo/image/upload/media/image/old_name.jpg',
            md5: '5f3409jg45jg5igj09g45',
            id: 56,
            tags: ['tag1'],
        );

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

    public function testRemoveTags(): void
    {
        $resource = new RemoteResource(
            remoteId: 'upload|image|old_name',
            type: 'image',
            url: 'https://res.cloudinary.com/demo/image/upload/media/image/old_name.jpg',
            md5: '5f3409jg45jg5igj09g45',
            id: 56,
            tags: ['tag1'],
        );

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

    public function testAddContextProperty(): void
    {
        $resource = new RemoteResource(
            remoteId: 'upload|image|old_name',
            type: 'image',
            url: 'https://res.cloudinary.com/demo/image/upload/media/image/old_name.jpg',
            md5: '5f3409jg45jg5igj09g45',
            id: 56,
            context: ['source' => 'test_source'],
        );

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

    public function testRemoveContextProperty(): void
    {
        $resource = new RemoteResource(
            remoteId: 'upload|image|old_name',
            type: 'image',
            url: 'https://res.cloudinary.com/demo/image/upload/media/image/old_name.jpg',
            md5: '5f3409jg45jg5igj09g45',
            id: 56,
            context: ['source' => 'test_source'],
        );

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
