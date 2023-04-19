<?php

declare(strict_types=1);

namespace Netgen\RemoteMedia\Tests\Form\DataTransformer;

use Netgen\RemoteMedia\API\ProviderInterface;
use Netgen\RemoteMedia\API\Values\CropSettings;
use Netgen\RemoteMedia\API\Values\Folder;
use Netgen\RemoteMedia\API\Values\RemoteResource;
use Netgen\RemoteMedia\API\Values\RemoteResourceLocation;
use Netgen\RemoteMedia\Exception\RemoteResourceLocationNotFoundException;
use Netgen\RemoteMedia\Exception\RemoteResourceNotFoundException;
use Netgen\RemoteMedia\Form\DataTransformer\RemoteMediaTransformer;
use Netgen\RemoteMedia\Tests\AbstractTestCase;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\MockObject\MockObject;
use Symfony\Component\Form\DataTransformerInterface;

#[CoversClass(RemoteMediaTransformer::class)]
class RemoteMediaTransformerTest extends AbstractTestCase
{
    protected DataTransformerInterface $dataTransformer;

    protected MockObject|ProviderInterface $providerMock;

    protected function setUp(): void
    {
        $this->providerMock = $this->createMock(ProviderInterface::class);

        $this->dataTransformer = new RemoteMediaTransformer($this->providerMock);
    }

    #[DataProvider('transformDataProvider')]
    public function testTransform(mixed $value, ?array $expectedData): void
    {
        self::assertSame(
            $expectedData,
            $this->dataTransformer->transform($value),
        );
    }

    public function testReverseTransformWithoutResource(): void
    {
        $data = [
            'locationId' => 5,
            'remoteId' => null,
            'type' => null,
            'tags' => null,
            'cropSettings' => null,
            'source' => null,
        ];

        self::assertNull($this->dataTransformer->reverseTransform($data));
    }

    public function testReverseTransformWithNewResource(): void
    {
        $data = [
            'locationId' => null,
            'remoteId' => 'upload|image|media/images/example.jpg',
            'type' => 'image',
            'altText' => 'Test alt text',
            'caption' => 'Test caption',
            'tags' => ['tag1', 'tag2'],
            'cropSettings' => '{"hero_image":{"x":10,"y":20,"w":1920,"h":1080}}',
            'source' => null,
        ];

        $resource = new RemoteResource(
            remoteId: 'upload|image|media/images/example.jpg',
            type: 'image',
            url: 'https://cloudinary.com/test/upload/image/media/images/example.jpg',
            md5: 'e522f43cf89aa0afd03387c37e2b6e29',
            name: 'example.jpg',
            folder: Folder::fromPath('media/images'),
        );

        $this->providerMock
            ->expects(self::once())
            ->method('loadByRemoteId')
            ->with('upload|image|media/images/example.jpg')
            ->willThrowException(new RemoteResourceNotFoundException('upload|image|media/images/example.jpg'));

        $this->providerMock
            ->expects(self::once())
            ->method('loadFromRemote')
            ->with('upload|image|media/images/example.jpg')
            ->willReturn($resource);

        $expectedLocation = new RemoteResourceLocation(
            new RemoteResource(
                remoteId: 'upload|image|media/images/example.jpg',
                type: 'image',
                url: 'https://cloudinary.com/test/upload/image/media/images/example.jpg',
                md5: 'e522f43cf89aa0afd03387c37e2b6e29',
                name: 'example.jpg',
                folder: Folder::fromPath('media/images'),
                altText: 'Test alt text',
                caption: 'Test caption',
                tags: ['tag1', 'tag2'],
            ),
            null,
            [
                new CropSettings('hero_image', 10, 20, 1920, 1080),
            ],
        );

        $this->providerMock
            ->expects(self::once())
            ->method('updateOnRemote')
            ->with($expectedLocation->getRemoteResource());

        self::assertRemoteResourceLocationSame(
            $expectedLocation,
            $this->dataTransformer->reverseTransform($data),
        );
    }

    public function testReverseTransformWithNonExistingResource(): void
    {
        $data = [
            'locationId' => null,
            'remoteId' => 'upload|image|media/images/example.jpg',
            'type' => 'image',
            'altText' => 'Test alt text',
            'caption' => 'Test caption',
            'tags' => ['tag1', 'tag2'],
            'cropSettings' => '{"hero_image":{"x":10,"y":20,"w":1920,"h":1080}}',
            'source' => null,
        ];

        $this->providerMock
            ->expects(self::once())
            ->method('loadByRemoteId')
            ->with('upload|image|media/images/example.jpg')
            ->willThrowException(new RemoteResourceNotFoundException('upload|image|media/images/example.jpg'));

        $this->providerMock
            ->expects(self::once())
            ->method('loadFromRemote')
            ->with('upload|image|media/images/example.jpg')
            ->willThrowException(new RemoteResourceNotFoundException('upload|image|media/images/example.jpg'));

        self::assertNull($this->dataTransformer->reverseTransform($data));
    }

    public function testReverseTransformWithExistingLocation(): void
    {
        $data = [
            'locationId' => 5,
            'remoteId' => 'upload|image|media/images/example.jpg',
            'type' => 'image',
            'tags' => ['tag2'],
            'cropSettings' => null,
        ];

        $location = new RemoteResourceLocation(
            new RemoteResource(
                remoteId: 'upload|image|media/images/example.jpg',
                type: 'image',
                url: 'https://cloudinary.com/test/upload/image/media/images/example.jpg',
                md5: 'e522f43cf89aa0afd03387c37e2b6e29',
                name: 'example.jpg',
                folder: Folder::fromPath('media/images'),
                tags: ['tag1', 'tag2', 'tag3'],
            ),
            null,
            [
                new CropSettings('hero_image', 10, 20, 1920, 1080),
            ],
        );

        $this->providerMock
            ->expects(self::once())
            ->method('loadByRemoteId')
            ->with('upload|image|media/images/example.jpg')
            ->willReturn($location->getRemoteResource());

        $this->providerMock
            ->expects(self::once())
            ->method('loadLocation')
            ->with(5)
            ->willReturn($location);

        $expectedLocation = new RemoteResourceLocation(
            new RemoteResource(
                remoteId: 'upload|image|media/images/example.jpg',
                type: 'image',
                url: 'https://cloudinary.com/test/upload/image/media/images/example.jpg',
                md5: 'e522f43cf89aa0afd03387c37e2b6e29',
                name: 'example.jpg',
                folder: Folder::fromPath('media/images'),
                tags: ['tag2'],
            ),
        );

        $this->providerMock
            ->expects(self::once())
            ->method('updateOnRemote')
            ->with($expectedLocation->getRemoteResource());

        self::assertRemoteResourceLocationSame(
            $expectedLocation,
            $this->dataTransformer->reverseTransform($data),
        );
    }

    public function testReverseTransformWithResourceNotExistingOnRemoteAnymore(): void
    {
        $data = [
            'locationId' => 5,
            'remoteId' => 'upload|image|media/images/example.jpg',
            'type' => 'image',
            'tags' => ['tag2'],
            'cropSettings' => null,
            'source' => 'test',
            'caption' => 'test caption',
        ];

        $location = new RemoteResourceLocation(
            new RemoteResource(
                remoteId: 'upload|image|media/images/example.jpg',
                type: 'image',
                url: 'https://cloudinary.com/test/upload/image/media/images/example.jpg',
                md5: 'e522f43cf89aa0afd03387c37e2b6e29',
                name: 'example.jpg',
                folder: Folder::fromPath('media/images'),
                tags: ['tag1', 'tag2', 'tag3'],
            ),
            'test',
            [
                new CropSettings('hero_image', 10, 20, 1920, 1080),
            ],
        );

        $this->providerMock
            ->expects(self::once())
            ->method('loadByRemoteId')
            ->with('upload|image|media/images/example.jpg')
            ->willReturn($location->getRemoteResource());

        $this->providerMock
            ->expects(self::once())
            ->method('loadLocation')
            ->with(5)
            ->willReturn($location);

        $this->providerMock
            ->expects(self::once())
            ->method('updateOnRemote')
            ->willThrowException(new RemoteResourceNotFoundException('upload|image|media/images/example.jpg'));

        $this->providerMock
            ->expects(self::once())
            ->method('remove')
            ->with($location->getRemoteResource());

        self::assertNull($this->dataTransformer->reverseTransform($data));
    }

    public function testReverseTransformWithExistingLocationMissingInDatabase(): void
    {
        $data = [
            'locationId' => 5,
            'remoteId' => 'upload|image|media/images/example.jpg',
            'type' => 'image',
            'tags' => ['tag2'],
            'altText' => 'Test alt text',
            'caption' => 'Test caption',
            'cropSettings' => null,
            'source' => 'test',
        ];

        $resource = new RemoteResource(
            remoteId: 'upload|image|media/images/example.jpg',
            type: 'image',
            url: 'https://cloudinary.com/test/upload/image/media/images/example.jpg',
            md5: 'e522f43cf89aa0afd03387c37e2b6e29',
            name: 'example.jpg',
            folder: Folder::fromPath('media/images'),
            altText: 'Test alt text',
            caption: 'Test caption',
            tags: ['tag2'],
        );

        $this->providerMock
            ->expects(self::once())
            ->method('loadByRemoteId')
            ->with('upload|image|media/images/example.jpg')
            ->willReturn($resource);

        $this->providerMock
            ->expects(self::once())
            ->method('loadLocation')
            ->with(5)
            ->willThrowException(new RemoteResourceLocationNotFoundException(5));

        $expectedLocation = new RemoteResourceLocation(
            new RemoteResource(
                remoteId: 'upload|image|media/images/example.jpg',
                type: 'image',
                url: 'https://cloudinary.com/test/upload/image/media/images/example.jpg',
                md5: 'e522f43cf89aa0afd03387c37e2b6e29',
                name: 'example.jpg',
                folder: Folder::fromPath('media/images'),
                altText: 'Test alt text',
                caption: 'Test caption',
                tags: ['tag2'],
            ),
            'test',
        );

        $this->providerMock
            ->expects(self::never())
            ->method('updateOnRemote')
            ->with($expectedLocation->getRemoteResource());

        self::assertRemoteResourceLocationSame(
            $expectedLocation,
            $this->dataTransformer->reverseTransform($data),
        );
    }

    public function testReverseTransformWithExistingLocationNewResource(): void
    {
        $data = [
            'locationId' => 5,
            'remoteId' => 'upload|image|media/images/new_image.jpg',
            'type' => 'image',
            'tags' => ['tag2'],
            'cropSettings' => null,
            'source' => 'some source',
        ];

        $newResource = new RemoteResource(
            remoteId: 'upload|image|media/images/new_image.jpg',
            type: 'image',
            url: 'https://cloudinary.com/test/upload/image/media/images/new_image.jpg',
            md5: 'a132fs3cf89aa0afd03387c32esb631c',
            name: 'new_image.jpg',
            folder: Folder::fromPath('media/images'),
            tags: ['tag1', 'tag2', 'tag3'],
        );

        $this->providerMock
            ->expects(self::once())
            ->method('loadByRemoteId')
            ->with('upload|image|media/images/new_image.jpg')
            ->willReturn($newResource);

        $location = new RemoteResourceLocation(
            new RemoteResource(
                remoteId: 'upload|image|media/images/example.jpg',
                type: 'image',
                url: 'https://cloudinary.com/test/upload/image/media/images/example.jpg',
                md5: 'e522f43cf89aa0afd03387c37e2b6e29',
                name: 'example.jpg',
                folder: Folder::fromPath('media/images'),
                altText: 'Test alt text',
                caption: 'Test caption',
                tags: ['tag1', 'tag2', 'tag3'],
            ),
            null,
            [
                new CropSettings('hero_image', 10, 20, 1920, 1080),
            ],
        );

        $this->providerMock
            ->expects(self::once())
            ->method('loadLocation')
            ->with(5)
            ->willReturn($location);

        $expectedLocation = new RemoteResourceLocation($newResource, 'some source');

        $this->providerMock
            ->expects(self::once())
            ->method('updateOnRemote')
            ->with($newResource);

        self::assertRemoteResourceLocationSame(
            $expectedLocation,
            $this->dataTransformer->reverseTransform($data),
        );
    }

    public static function transformDataProvider(): array
    {
        return [
            [
                new RemoteResource(
                    remoteId: 'upload|image|media/images/example.jpg',
                    type: 'image',
                    url: 'https://cloudinary.com/test/upload/image/media/images/example.jpg',
                    md5: 'e522f43cf89aa0afd03387c37e2b6e29',
                    name: 'example.jpg',
                ),
                null,
            ],
            [
                'test',
                null,
            ],
            [
                new RemoteResourceLocation(
                    new RemoteResource(
                        remoteId: 'upload|image|media/images/example.jpg',
                        type: 'image',
                        url: 'https://cloudinary.com/test/upload/image/media/images/example.jpg',
                        md5: 'e522f43cf89aa0afd03387c37e2b6e29',
                        name: 'example.jpg',
                        folder: Folder::fromPath('media/images'),
                        altText: 'Test alt text',
                        caption: 'Test caption',
                        tags: ['tag1', 'tag2'],
                    ),
                    null,
                    [
                        new CropSettings('hero_image', 10, 20, 1920, 1080),
                    ],
                ),
                [
                    'locationId' => null,
                    'remoteId' => 'upload|image|media/images/example.jpg',
                    'type' => 'image',
                    'altText' => 'Test alt text',
                    'caption' => 'Test caption',
                    'tags' => ['tag1', 'tag2'],
                    'cropSettings' => '{"hero_image":{"x":10,"y":20,"w":1920,"h":1080}}',
                    'source' => null,
                ],
            ],
            [
                new RemoteResourceLocation(
                    new RemoteResource(
                        remoteId: 'upload|image|media/images/example.jpg',
                        type: 'image',
                        url: 'https://cloudinary.com/test/upload/image/media/images/example.jpg',
                        md5: 'e522f43cf89aa0afd03387c37e2b6e29',
                        name: 'example.jpg',
                    ),
                    'my_source',
                ),
                [
                    'locationId' => null,
                    'remoteId' => 'upload|image|media/images/example.jpg',
                    'type' => 'image',
                    'altText' => null,
                    'caption' => null,
                    'tags' => [],
                    'cropSettings' => '[]',
                    'source' => 'my_source',
                ],
            ],
        ];
    }
}
