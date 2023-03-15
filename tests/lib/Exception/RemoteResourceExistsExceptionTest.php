<?php

declare(strict_types=1);

namespace Netgen\RemoteMedia\Tests\Exception;

use Netgen\RemoteMedia\API\Values\RemoteResource;
use Netgen\RemoteMedia\Exception\RemoteResourceExistsException;
use Netgen\RemoteMedia\Tests\AbstractTestCase;
use PHPUnit\Framework\Attributes\CoversClass;

#[CoversClass(RemoteResourceExistsException::class)]
final class RemoteResourceExistsExceptionTest extends AbstractTestCase
{
    public function testException(): void
    {
        $this->expectException(RemoteResourceExistsException::class);
        $this->expectExceptionMessage('Remote resource with ID "upload|image|media/example" already exists.');

        $resource = new RemoteResource(
            remoteId: 'upload|image|media/example',
            type: 'image',
            url: 'https://res.cloudinary.com/demo/upload/image/media/example',
            md5: 'e522f43cf89aa0afd03387c37e2b6e12',
            name: 'example',
            size: 120253,
        );

        throw new RemoteResourceExistsException($resource);
    }

    public function testGetResource(): void
    {
        $resource = new RemoteResource(
            remoteId: 'upload|image|media/example',
            type: 'image',
            url: 'https://res.cloudinary.com/demo/upload/image/media/example',
            md5: 'e522f43cf89aa0afd03387c37e2b6e12',
            name: 'example',
            size: 120253,
        );

        $exception = new RemoteResourceExistsException($resource);

        self::assertRemoteResourceSame(
            $resource,
            $exception->getRemoteResource(),
        );
    }
}
