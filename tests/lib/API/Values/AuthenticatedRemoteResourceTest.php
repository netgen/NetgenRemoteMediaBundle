<?php

declare(strict_types=1);

namespace Netgen\RemoteMedia\Tests\API\Values;

use Netgen\RemoteMedia\API\Values\AuthenticatedRemoteResource;
use Netgen\RemoteMedia\API\Values\AuthToken;
use Netgen\RemoteMedia\API\Values\Folder;
use Netgen\RemoteMedia\API\Values\RemoteResource;
use Netgen\RemoteMedia\Tests\AbstractTestCase;
use PHPUnit\Framework\Attributes\CoversClass;

#[CoversClass(AuthenticatedRemoteResource::class)]
final class AuthenticatedRemoteResourceTest extends AbstractTestCase
{
    public function testConstruction(): void
    {
        $resource = new RemoteResource(
            remoteId: 'authenticated|image|images/test.jpg',
            type: 'image',
            url: 'https://cloudinary.com/test/authenticated/image/images/test.jpg',
            md5: 'e522f43cf89aa0afd03387c37e2b6e29',
            name: 'test.jpg',
            folder: Folder::fromPath('image'),
        );

        $authenticatedUrl = 'https://cloudinary.com/test/authenticated/image/images/test.jpg?_token=656e35f16745b3a97f78cb3fc8941abb';

        $token = AuthToken::fromDuration(100);

        $authenticatedResource = new AuthenticatedRemoteResource($resource, $authenticatedUrl, $token);

        self::assertSame(
            $resource->getRemoteId(),
            $authenticatedResource->getRemoteId(),
        );

        self::assertSame(
            $resource->getType(),
            $authenticatedResource->getType(),
        );

        self::assertSame(
            $resource->getName(),
            $authenticatedResource->getName(),
        );

        self::assertSame(
            $resource->getMd5(),
            $authenticatedResource->getMd5(),
        );

        self::assertFolderSame(
            $resource->getFolder(),
            $authenticatedResource->getFolder(),
        );

        self::assertSame(
            $authenticatedUrl,
            $authenticatedResource->getUrl(),
        );

        self::assertSame(
            $token,
            $authenticatedResource->getToken(),
        );

        self::assertTrue($authenticatedResource->isValid());
    }
}
