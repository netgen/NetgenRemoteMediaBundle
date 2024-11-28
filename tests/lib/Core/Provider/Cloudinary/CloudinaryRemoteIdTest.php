<?php

declare(strict_types=1);

namespace Netgen\RemoteMedia\Tests\Core\Provider\Cloudinary;

use Netgen\RemoteMedia\API\Values\Folder;
use Netgen\RemoteMedia\Core\Provider\Cloudinary\CloudinaryProvider;
use Netgen\RemoteMedia\Core\Provider\Cloudinary\CloudinaryRemoteId;
use Netgen\RemoteMedia\Exception\Cloudinary\InvalidRemoteIdException;
use Netgen\RemoteMedia\Exception\NotSupportedException;
use Netgen\RemoteMedia\Tests\AbstractTestCase;
use PHPUnit\Framework\Attributes\CoversClass;

#[CoversClass(CloudinaryRemoteId::class)]
final class CloudinaryRemoteIdTest extends AbstractTestCase
{
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

        self::assertNull($remoteId->getFolder());
    }

    public function testFromCloudinaryDataRenameNotification(): void
    {
        $data = [
            'from_public_id' => 'folder/my_test_image.jpg',
            'to_public_id' => 'folder/subfolder/my_test_image_2.jpg',
            'resource_type' => 'image',
            'type' => 'upload',
            'secure_url' => 'https://cloudinary.com/cloudname/upload/image/folder/subfolder/my_test_image_2.jpg',
            'size' => 23456,
        ];

        $remoteId = CloudinaryRemoteId::fromCloudinaryData($data);

        self::assertSame(
            'upload|image|folder/subfolder/my_test_image_2.jpg',
            $remoteId->getRemoteId(),
        );

        self::assertSame(
            'folder/subfolder/my_test_image_2.jpg',
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

        self::assertFolderSame(
            Folder::fromPath('folder/subfolder'),
            $remoteId->getFolder(),
        );
    }

    public function testFromRemoteId(): void
    {
        $remoteId = CloudinaryRemoteId::fromRemoteId('private|video|media/videos/my_test_video.mp4');

        self::assertSame(
            'private|video|media/videos/my_test_video.mp4',
            $remoteId->getRemoteId(),
        );

        self::assertSame(
            'media/videos/my_test_video.mp4',
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

        self::assertFolderSame(
            Folder::fromPath('media/videos'),
            $remoteId->getFolder(),
        );
    }

    public function testFromRemoteIdInDynamicFolderMode(): void
    {
        $remoteId = CloudinaryRemoteId::fromRemoteId(
            'private|video|media/videos/my_test_video.mp4',
            CloudinaryProvider::FOLDER_MODE_DYNAMIC,
        );

        self::assertSame(
            'private|video|media/videos/my_test_video.mp4',
            $remoteId->getRemoteId(),
        );

        self::assertSame(
            'media/videos/my_test_video.mp4',
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

        self::expectException(NotSupportedException::class);
        self::expectExceptionMessage('Provider "Cloudinary" does not support "fetching folder from path in "dynamic" folder mode".');

        $remoteId->getFolder();
    }

    public function testFromInvalidRemoteId(): void
    {
        self::expectException(InvalidRemoteIdException::class);

        CloudinaryRemoteId::fromRemoteId('some_image.jpg');
    }

    public function testFromHalfInvalidRemoteId(): void
    {
        self::expectException(InvalidRemoteIdException::class);

        CloudinaryRemoteId::fromRemoteId('image|some_image.jpg');
    }
}
