<?php

declare(strict_types=1);

namespace Netgen\RemoteMedia\Tests\Core\Provider\Cloudinary;

use Cloudinary\Api\Response;
use Netgen\RemoteMedia\Core\Provider\Cloudinary\CloudinaryRemoteId;
use Netgen\RemoteMedia\Exception\Cloudinary\InvalidRemoteIdException;
use PHPUnit\Framework\TestCase;
use stdClass;
use function json_encode;

final class CloudinaryRemoteIdTest extends TestCase
{
    /**
     * @covers \Netgen\RemoteMedia\Core\Provider\Cloudinary\CloudinaryRemoteId::__construct
     * @covers \Netgen\RemoteMedia\Core\Provider\Cloudinary\CloudinaryRemoteId::fromCloudinaryData
     * @covers \Netgen\RemoteMedia\Core\Provider\Cloudinary\CloudinaryRemoteId::getRemoteId
     * @covers \Netgen\RemoteMedia\Core\Provider\Cloudinary\CloudinaryRemoteId::getResourceId
     * @covers \Netgen\RemoteMedia\Core\Provider\Cloudinary\CloudinaryRemoteId::getResourceType
     * @covers \Netgen\RemoteMedia\Core\Provider\Cloudinary\CloudinaryRemoteId::getType
     */
    public function testFromCloudinaryData(): void
    {
        $data = [
            'public_id' => 'my_test_image.jpg',
            'resource_type' => 'image',
            'type' => 'upload',
            'secure_url' => 'https://cloudinary.com/cloudname/upload/image/my_test_image.jpg',
            'size' => 23456,
        ];

        $remoteId = CloudinaryRemoteId::fromCloudinaryData($data);

        self::assertSame(
            'upload|image|my_test_image.jpg',
            $remoteId->getRemoteId(),
        );

        self::assertSame(
            'my_test_image.jpg',
            $remoteId->getResourceId(),
        );

        self::assertSame(
            'image',
            $remoteId->getResourceType(),
        );

        self::assertSame(
            'upload',
            $remoteId->getType(),
        );
    }

    /**
     * @covers \Netgen\RemoteMedia\Core\Provider\Cloudinary\CloudinaryRemoteId::__construct
     * @covers \Netgen\RemoteMedia\Core\Provider\Cloudinary\CloudinaryRemoteId::fromRemoteId
     * @covers \Netgen\RemoteMedia\Core\Provider\Cloudinary\CloudinaryRemoteId::getRemoteId
     * @covers \Netgen\RemoteMedia\Core\Provider\Cloudinary\CloudinaryRemoteId::getResourceId
     * @covers \Netgen\RemoteMedia\Core\Provider\Cloudinary\CloudinaryRemoteId::getResourceType
     * @covers \Netgen\RemoteMedia\Core\Provider\Cloudinary\CloudinaryRemoteId::getType
     */
    public function testFromRemoteId(): void
    {
        $remoteId = CloudinaryRemoteId::fromRemoteId('private|video|my_test_video.mp4');

        self::assertSame(
            'private|video|my_test_video.mp4',
            $remoteId->getRemoteId(),
        );

        self::assertSame(
            'my_test_video.mp4',
            $remoteId->getResourceId(),
        );

        self::assertSame(
            'video',
            $remoteId->getResourceType(),
        );

        self::assertSame(
            'private',
            $remoteId->getType(),
        );
    }

    /**
     * @covers \Netgen\RemoteMedia\Core\Provider\Cloudinary\CloudinaryRemoteId::fromRemoteId
     */
    public function testFromInvalidRemoteId(): void
    {
        self::expectException(InvalidRemoteIdException::class);

        CloudinaryRemoteId::fromRemoteId('some_image.jpg');
    }

    /**
     * @covers \Netgen\RemoteMedia\Core\Provider\Cloudinary\CloudinaryRemoteId::fromRemoteId
     */
    public function testFromHalfInvalidRemoteId(): void
    {
        self::expectException(InvalidRemoteIdException::class);

        CloudinaryRemoteId::fromRemoteId('image|some_image.jpg');
    }
}
