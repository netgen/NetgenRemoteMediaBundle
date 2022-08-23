<?php

declare(strict_types=1);

namespace Netgen\RemoteMedia\Tests\API\Values;

use Netgen\RemoteMedia\API\Values\RemoteResource;
use Netgen\RemoteMedia\API\Values\RemoteResourceVariation;
use Netgen\RemoteMedia\Tests\AbstractTest;

final class RemoteResourceVariationTest extends AbstractTest
{
    private RemoteResource $remoteResource;

    protected function setUp(): void
    {
        $this->remoteResource = new RemoteResource([
            'remoteId' => 'test_image',
            'type' => 'image',
            'url' => 'https://cloudinary.com/test/test_image',
        ]);
    }

    /**
     * @covers \Netgen\RemoteMedia\API\Values\RemoteResourceVariation::__construct
     * @covers \Netgen\RemoteMedia\API\Values\RemoteResourceVariation::getRemoteResource
     * @covers \Netgen\RemoteMedia\API\Values\RemoteResourceVariation::getUrl
     */
    public function test(): void
    {
        $variation = new RemoteResourceVariation($this->remoteResource, 'https://cloudinary.com/test/variation/test_image');

        self::assertRemoteResourceSame(
            $this->remoteResource,
            $variation->getRemoteResource(),
        );

        self::assertSame(
            'https://cloudinary.com/test/variation/test_image',
            $variation->getUrl(),
        );
    }

    /**
     * @covers \Netgen\RemoteMedia\API\Values\RemoteResourceVariation::__construct
     * @covers \Netgen\RemoteMedia\API\Values\RemoteResourceVariation::fromResource
     * @covers \Netgen\RemoteMedia\API\Values\RemoteResourceVariation::getUrl
     */
    public function testFromResource(): void
    {
        $variation = RemoteResourceVariation::fromResource($this->remoteResource);

        self::assertRemoteResourceSame(
            $this->remoteResource,
            $variation->getRemoteResource(),
        );

        self::assertSame(
            'https://cloudinary.com/test/test_image',
            $variation->getUrl(),
        );
    }
}
