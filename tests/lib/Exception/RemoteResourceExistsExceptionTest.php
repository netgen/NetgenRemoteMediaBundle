<?php

declare(strict_types=1);

namespace Netgen\RemoteMedia\Tests\Exception;

use Netgen\RemoteMedia\API\Values\RemoteResource;
use Netgen\RemoteMedia\Exception\RemoteResourceExistsException;
use Netgen\RemoteMedia\Tests\AbstractTest;

final class RemoteResourceExistsExceptionTest extends AbstractTest
{
    /**
     * @covers \Netgen\RemoteMedia\Exception\RemoteResourceExistsException::__construct
     */
    public function testException(): void
    {
        $this->expectException(RemoteResourceExistsException::class);
        $this->expectExceptionMessage('Remote resource with ID "upload|image|media/example" already exists.');

        $resource = new RemoteResource([
            'remoteId' => 'upload|image|media/example',
            'type' => 'image',
            'url' => 'https://res.cloudinary.com/demo/upload/image/media/example',
            'name' => 'example',
            'size' => 120253,
            'md5' => 'e522f43cf89aa0afd03387c37e2b6e12',
        ]);

        throw new RemoteResourceExistsException($resource);
    }

    /**
     * @covers \Netgen\RemoteMedia\Exception\RemoteResourceExistsException::__construct
     * @covers \Netgen\RemoteMedia\Exception\RemoteResourceExistsException::getRemoteResource
     */
    public function testGetResource(): void
    {
        $resource = new RemoteResource([
            'remoteId' => 'upload|image|media/example',
            'type' => 'image',
            'url' => 'https://res.cloudinary.com/demo/upload/image/media/example',
            'name' => 'example',
            'size' => 120253,
            'md5' => 'e522f43cf89aa0afd03387c37e2b6e12',
        ]);

        $exception = new RemoteResourceExistsException($resource);

        self::assertRemoteResourceSame(
            $resource,
            $exception->getRemoteResource(),
        );
    }
}
