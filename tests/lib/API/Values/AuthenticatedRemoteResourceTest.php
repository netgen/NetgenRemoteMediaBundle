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

        $authenticatedUrl = 'https://cloudinary.com/test/authenticated/image/images/test.jpg?_token=dsr43t4rerf4345';

        $token = AuthToken::fromDuration(100);

        $authenticatedResource = new AuthenticatedRemoteResource($resource, $authenticatedUrl, $token);

        self::assertRemoteResourceSame(
            $resource,
            $authenticatedResource->getRemoteResource(),
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
