<?php

declare(strict_types=1);

namespace Netgen\RemoteMedia\Tests\API\Values;

use Netgen\RemoteMedia\API\Values\Folder;
use Netgen\RemoteMedia\API\Values\RemoteResource;
use Netgen\RemoteMedia\API\Values\RemoteResourceVariation;
use Netgen\RemoteMedia\Tests\AbstractTest;

final class RemoteResourceVariationTest extends AbstractTest
{
    /**
     * @covers \Netgen\RemoteMedia\API\Values\RemoteResourceVariation::__construct
     * @covers \Netgen\RemoteMedia\API\Values\RemoteResourceVariation::getRemoteResource
     * @covers \Netgen\RemoteMedia\API\Values\RemoteResourceVariation::getUrl
     */
    public function testConstruction(): void
    {
        $resource = new RemoteResource([
            'remoteId' => 'image/test.jpg',
            'type' => 'img',
            'url' => 'https://cloudinary.com/test/upload/image/image/test.jpg',
            'name' => 'test.jpg',
            'folder' => Folder::fromPath('image'),
        ]);

        $variationUrl = 'https://cloudinary.com/test/upload/image/c_5_10/q_auto/image/test.jpg';

        $variation = new RemoteResourceVariation($resource, $variationUrl);

        self::assertRemoteResourceSame(
            $resource,
            $variation->getRemoteResource(),
        );

        self::assertSame(
            $variationUrl,
            $variation->getUrl(),
        );
    }

    /**
     * @covers \Netgen\RemoteMedia\API\Values\RemoteResourceVariation::__construct
     * @covers \Netgen\RemoteMedia\API\Values\RemoteResourceVariation::fromResource
     * @covers \Netgen\RemoteMedia\API\Values\RemoteResourceVariation::getRemoteResource
     * @covers \Netgen\RemoteMedia\API\Values\RemoteResourceVariation::getUrl
     */
    public function testConstructionFromRemoteResource(): void
    {
        $resource = new RemoteResource([
            'remoteId' => 'image/test.jpg',
            'type' => 'img',
            'url' => 'https://cloudinary.com/test/upload/image/image/test.jpg',
            'name' => 'test.jpg',
            'folder' => Folder::fromPath('image'),
        ]);

        $variation = RemoteResourceVariation::fromResource($resource);

        self::assertRemoteResourceSame(
            $resource,
            $variation->getRemoteResource(),
        );

        self::assertSame(
            'https://cloudinary.com/test/upload/image/image/test.jpg',
            $variation->getUrl(),
        );
    }
}
