<?php

declare(strict_types=1);

namespace Netgen\RemoteMedia\Tests\API\Values;

use Netgen\RemoteMedia\API\Values\Folder;
use Netgen\RemoteMedia\API\Values\RemoteResource;
use Netgen\RemoteMedia\API\Values\RemoteResourceVariation;
use Netgen\RemoteMedia\Tests\AbstractTestCase;
use PHPUnit\Framework\Attributes\CoversClass;

#[CoversClass(RemoteResourceVariation::class)]
final class RemoteResourceVariationTest extends AbstractTestCase
{
    public function testConstruction(): void
    {
        $resource = new RemoteResource(
            remoteId: 'image/test.jpg',
            type: 'img',
            url: 'https://cloudinary.com/test/upload/image/image/test.jpg',
            md5: 'e522f43cf89aa0afd03387c37e2b6e29',
            name: 'test.jpg',
            folder: Folder::fromPath('image'),
        );

        $variationUrl = 'https://cloudinary.com/test/upload/image/c_120_160/q_auto/image/test.jpg';

        $transformations = [
            'crop' => 'fit',
            'width' => 160,
            'height' => 120,
        ];

        $variation = new RemoteResourceVariation($resource, $variationUrl, $transformations);

        self::assertRemoteResourceSame(
            $resource,
            $variation->getRemoteResource(),
        );

        self::assertSame(
            $variationUrl,
            $variation->getUrl(),
        );

        self::assertSame(
            $transformations,
            $variation->getTransformations(),
        );
    }

    public function testConstructionFromRemoteResource(): void
    {
        $resource = new RemoteResource(
            remoteId: 'image/test.jpg',
            type: 'img',
            url: 'https://cloudinary.com/test/upload/image/image/test.jpg',
            md5: 'e522f43cf89aa0afd03387c37e2b6e29',
            name: 'test.jpg',
            folder: Folder::fromPath('image'),
        );

        $variation = RemoteResourceVariation::fromResource($resource);

        self::assertRemoteResourceSame(
            $resource,
            $variation->getRemoteResource(),
        );

        self::assertSame(
            'https://cloudinary.com/test/upload/image/image/test.jpg',
            $variation->getUrl(),
        );

        self::assertEmpty($variation->getTransformations());
    }
}
