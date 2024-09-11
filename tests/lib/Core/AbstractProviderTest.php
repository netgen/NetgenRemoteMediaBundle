<?php

declare(strict_types=1);

namespace Netgen\RemoteMedia\Tests\Core;

use DateTimeImmutable;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\Persistence\ObjectRepository;
use Netgen\RemoteMedia\API\Factory\DateTime as DateTimeFactoryInterface;
use Netgen\RemoteMedia\API\Upload\FileStruct;
use Netgen\RemoteMedia\API\Upload\ResourceStruct;
use Netgen\RemoteMedia\API\Values\CropSettings;
use Netgen\RemoteMedia\API\Values\Folder;
use Netgen\RemoteMedia\API\Values\RemoteResource;
use Netgen\RemoteMedia\API\Values\RemoteResourceLocation;
use Netgen\RemoteMedia\API\Values\RemoteResourceVariation;
use Netgen\RemoteMedia\Core\AbstractProvider;
use Netgen\RemoteMedia\Core\Resolver\Variation as VariationResolver;
use Netgen\RemoteMedia\Core\Transformation\HandlerInterface;
use Netgen\RemoteMedia\Core\Transformation\Registry;
use Netgen\RemoteMedia\Exception\NamedRemoteResourceLocationNotFoundException;
use Netgen\RemoteMedia\Exception\NamedRemoteResourceNotFoundException;
use Netgen\RemoteMedia\Exception\NotSupportedException;
use Netgen\RemoteMedia\Exception\RemoteResourceLocationNotFoundException;
use Netgen\RemoteMedia\Exception\RemoteResourceNotFoundException;
use Netgen\RemoteMedia\Tests\AbstractTestCase;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\MockObject\MockObject;
use Psr\Log\LoggerInterface;
use Psr\Log\NullLogger;
use RuntimeException;

use function sprintf;

#[CoversClass(AbstractProvider::class)]
final class AbstractProviderTest extends AbstractTestCase
{
    private AbstractProvider $provider;

    private EntityManagerInterface|MockObject $entityManager;

    private DateTimeFactoryInterface|MockObject $dateTimeFactory;

    private HandlerInterface|MockObject $cropHandler;

    private HandlerInterface|MockObject $formatHandler;

    private LoggerInterface|MockObject $logger;

    private MockObject|ObjectRepository $resourceRepository;

    private MockObject|ObjectRepository $locationRepository;

    protected function setUp(): void
    {
        $this->entityManager = $this->createMock(EntityManagerInterface::class);
        $this->dateTimeFactory = $this->createMock(DateTimeFactoryInterface::class);
        $this->logger = $this->createMock(LoggerInterface::class);
        $this->resourceRepository = $this->createMock(ObjectRepository::class);
        $this->locationRepository = $this->createMock(ObjectRepository::class);
        $this->cropHandler = $this->createMock(HandlerInterface::class);
        $this->formatHandler = $this->createMock(HandlerInterface::class);

        $registry = new Registry();
        $registry->addHandler('cloudinary', 'crop', $this->cropHandler);
        $registry->addHandler('cloudinary', 'format', $this->formatHandler);

        $variationResolver = new VariationResolver(
            $registry,
            new NullLogger(),
            [
                'default' => [
                    'banner' => [
                        'transformations' => [
                            'crop' => [100, 100],
                        ],
                    ],
                ],
                'article' => [
                    'small' => [
                        'transformations' => [
                            'crop' => [200, 100],
                        ],
                    ],
                    'jpeg' => [
                        'transformations' => [
                            'format' => ['jpeg'],
                        ],
                    ],
                    'mp4' => [
                        'transformations' => [
                            'format' => ['mp4'],
                        ],
                    ],
                ],
            ],
        );

        $this->entityManager
            ->expects(self::exactly(2))
            ->method('getRepository')
            ->willReturnMap(
                [
                    [RemoteResource::class, $this->resourceRepository],
                    [RemoteResourceLocation::class, $this->locationRepository],
                ],
            );

        $namedRemoteResources = [
            'test_image' => 'upload|image|media/images/test_image.jpg',
            'test_image2' => 'upload|image|media/images/test_image2.jpg',
        ];

        $namedRemoteResourceLocations = [
            'test_image_location' => [
                'resource_remote_id' => 'upload|image|media/images/test_image.jpg',
                'source' => 'test_image_location_source',
            ],
            'test_image_location2' => [
                'resource_remote_id' => 'upload|image|media/images/test_image2.jpg',
                'watermark_text' => 'This is some watermark',
            ],
        ];

        $this->provider = $this
            ->getMockForAbstractClass(
                AbstractProvider::class,
                [
                    new Registry(),
                    $variationResolver,
                    $this->entityManager,
                    $this->dateTimeFactory,
                    $namedRemoteResources,
                    $namedRemoteResourceLocations,
                    $this->logger,
                    true,
                ],
            );
    }

    public function testListFolders(): void
    {
        $folders = [
            Folder::fromPath('media'),
        ];

        $this->provider
            ->expects(self::once())
            ->method('supportsFolders')
            ->willReturn(true);

        $this->provider
            ->expects(self::once())
            ->method('internalListFolders')
            ->with(null)
            ->willReturn($folders);

        self::assertSame(
            $folders,
            $this->provider->listFolders(),
        );
    }

    public function testListFoldersBelowParent(): void
    {
        $parent = Folder::fromPath('media');

        $folders = [
            Folder::fromPath('media/images'),
            Folder::fromPath('media/videos'),
            Folder::fromPath('media/files'),
        ];

        $this->provider
            ->expects(self::once())
            ->method('supportsFolders')
            ->willReturn(true);

        $this->provider
            ->expects(self::once())
            ->method('internalListFolders')
            ->with($parent)
            ->willReturn($folders);

        self::assertSame(
            $folders,
            $this->provider->listFolders($parent),
        );
    }

    public function testListFoldersUnsupported(): void
    {
        $this->provider
            ->expects(self::once())
            ->method('supportsFolders')
            ->willReturn(false);

        $this->provider
            ->expects(self::once())
            ->method('getIdentifier')
            ->willReturn('cloudinary');

        self::expectException(NotSupportedException::class);
        self::expectExceptionMessage('Provider "cloudinary" does not support "folders".');

        $this->provider->listFolders();
    }

    public function testCreateFolder(): void
    {
        $folder = Folder::fromPath('media');

        $this->provider
            ->expects(self::once())
            ->method('supportsFolders')
            ->willReturn(true);

        $this->provider
            ->expects(self::once())
            ->method('internalCreateFolder')
            ->with('media')
            ->willReturn($folder);

        self::assertSame(
            $folder,
            $this->provider->createFolder('media'),
        );
    }

    public function testCreateFolderBelowParent(): void
    {
        $parent = Folder::fromPath('media');
        $folder = Folder::fromPath('media/images');

        $this->provider
            ->expects(self::once())
            ->method('supportsFolders')
            ->willReturn(true);

        $this->provider
            ->expects(self::once())
            ->method('internalCreateFolder')
            ->with('images', $parent)
            ->willReturn($folder);

        self::assertSame(
            $folder,
            $this->provider->createFolder('images', $parent),
        );
    }

    public function testCreateFolderUnsupported(): void
    {
        $this->provider
            ->expects(self::once())
            ->method('supportsFolders')
            ->willReturn(false);

        $this->provider
            ->expects(self::once())
            ->method('getIdentifier')
            ->willReturn('cloudinary');

        self::expectException(NotSupportedException::class);
        self::expectExceptionMessage('Provider "cloudinary" does not support "folders".');

        $this->provider->createFolder('media');
    }

    public function testCountInFolder(): void
    {
        $folder = Folder::fromPath('media');

        $this->provider
            ->expects(self::once())
            ->method('supportsFolders')
            ->willReturn(true);

        $this->provider
            ->expects(self::once())
            ->method('internalCountInFolder')
            ->with($folder)
            ->willReturn(10);

        self::assertSame(
            10,
            $this->provider->countInFolder($folder),
        );
    }

    public function testCountInFolderUnsupported(): void
    {
        $folder = Folder::fromPath('media');

        $this->provider
            ->expects(self::once())
            ->method('supportsFolders')
            ->willReturn(false);

        $this->provider
            ->expects(self::once())
            ->method('getIdentifier')
            ->willReturn('cloudinary');

        self::expectException(NotSupportedException::class);
        self::expectExceptionMessage('Provider "cloudinary" does not support "folders".');

        $this->provider->countInFolder($folder);
    }

    public function testListTags(): void
    {
        $tags = ['tag1', 'tag2'];

        $this->provider
            ->expects(self::once())
            ->method('supportsTags')
            ->willReturn(true);

        $this->provider
            ->expects(self::once())
            ->method('internalListTags')
            ->willReturn($tags);

        self::assertSame(
            $tags,
            $this->provider->listTags(),
        );
    }

    public function testListTagsUnsupported(): void
    {
        $this->provider
            ->expects(self::once())
            ->method('supportsTags')
            ->willReturn(false);

        $this->provider
            ->expects(self::once())
            ->method('getIdentifier')
            ->willReturn('cloudinary');

        self::expectException(NotSupportedException::class);
        self::expectExceptionMessage('Provider "cloudinary" does not support "tags".');

        $this->provider->listTags();
    }

    public function testLoad(): void
    {
        $remoteResource = new RemoteResource(
            remoteId: 'test_image.jpg',
            type: 'image',
            url: 'https://cloudinary.com/upload/image/test_image.jpg',
            md5: 'e522f43cf89aa0afd03387c37e2b6e29',
            id: 15,
            name: 'test_image.jpg',
            size: 200,
        );

        $this->resourceRepository
            ->expects(self::once())
            ->method('find')
            ->with(15)
            ->willReturn($remoteResource);

        $returnedResource = $this->provider->load(15);

        self::assertRemoteResourceSame(
            $remoteResource,
            $returnedResource,
        );
    }

    public function testLoadNotFound(): void
    {
        $this->resourceRepository
            ->expects(self::once())
            ->method('find')
            ->with(20)
            ->willReturn(null);

        self::expectException(RemoteResourceNotFoundException::class);
        self::expectExceptionMessage('Remote resource with ID "20" not found.');

        $this->provider->load(20);
    }

    public function testLoadByRemoteId(): void
    {
        $remoteResource = new RemoteResource(
            remoteId: 'test_image.jpg',
            type: 'image',
            url: 'https://cloudinary.com/upload/image/test_image.jpg',
            md5: 'e522f43cf89aa0afd03387c37e2b6e29',
            id: 15,
            name: 'test_image.jpg',
            size: 200,
        );

        $this->resourceRepository
            ->expects(self::once())
            ->method('findOneBy')
            ->with(['remoteId' => 'test_image.jpg'])
            ->willReturn($remoteResource);

        $returnedResource = $this->provider->loadByRemoteId('test_image.jpg');

        self::assertRemoteResourceSame(
            $remoteResource,
            $returnedResource,
        );
    }

    public function testLoadByRemoteIdNotFound(): void
    {
        $this->resourceRepository
            ->expects(self::once())
            ->method('findOneBy')
            ->with(['remoteId' => 'test_image_2.jpg'])
            ->willReturn(null);

        self::expectException(RemoteResourceNotFoundException::class);
        self::expectExceptionMessage('Remote resource with ID "test_image_2.jpg" not found.');

        $this->provider->loadByRemoteId('test_image_2.jpg');
    }

    public function testStoreExisting(): void
    {
        $remoteResource = new RemoteResource(
            remoteId: 'test_image.jpg',
            type: 'image',
            url: 'https://cloudinary.com/upload/image/test_image.jpg',
            md5: 'e522f43cf89aa0afd03387c37e2b6e29',
            id: 15,
            name: 'test_image.jpg',
            size: 200,
        );

        $this->entityManager
            ->expects(self::once())
            ->method('persist')
            ->with($remoteResource);

        $this->entityManager
            ->expects(self::once())
            ->method('flush');

        self::assertRemoteResourceSame(
            $remoteResource,
            $this->provider->store($remoteResource),
        );
    }

    public function testStoreNew(): void
    {
        $remoteResource = new RemoteResource(
            remoteId: 'test_image.jpg',
            type: 'image',
            url: 'https://cloudinary.com/upload/image/test_image.jpg',
            md5: 'e522f43cf89aa0afd03387c37e2b6e29',
            name: 'test_image.jpg',
            size: 200,
        );

        $this->resourceRepository
            ->expects(self::once())
            ->method('findOneBy')
            ->with(['remoteId' => $remoteResource->getRemoteId()])
            ->willReturn(null);

        $this->entityManager
            ->expects(self::once())
            ->method('persist')
            ->with($remoteResource);

        $this->entityManager
            ->expects(self::once())
            ->method('flush');

        self::assertRemoteResourceSame(
            $remoteResource,
            $this->provider->store($remoteResource),
        );
    }

    public function testStoreExistingRemoteId(): void
    {
        $oldRemoteResource = new RemoteResource(
            remoteId: 'test_image.jpg',
            type: 'image',
            url: 'https://cloudinary.com/upload/image/test_image.jpg',
            md5: 'e522f43cf89aa0afd03387c37e2b6e29',
            id: 15,
            name: 'test_image.jpg',
            size: 200,
        );

        $remoteResource = new RemoteResource(
            remoteId: 'test_image.jpg',
            type: 'image',
            url: 'https://cloudinary.com/upload/image/test_image_2.jpg',
            md5: 'e522f43cf89aa0afd03387c37e2b6e29',
            name: 'test_image.jpg',
            size: 250,
        );

        $dateTime = new DateTimeImmutable('now');

        $newRemoteResource = new RemoteResource(
            remoteId: 'test_image.jpg',
            type: 'image',
            url: 'https://cloudinary.com/upload/image/test_image_2.jpg',
            md5: 'e522f43cf89aa0afd03387c37e2b6e29',
            id: 15,
            name: 'test_image.jpg',
            size: 250,
        );

        $newRemoteResource->setUpdatedAt($dateTime);

        $this->resourceRepository
            ->expects(self::once())
            ->method('findOneBy')
            ->with(['remoteId' => $remoteResource->getRemoteId()])
            ->willReturn($oldRemoteResource);

        $this->dateTimeFactory
            ->expects(self::once())
            ->method('createCurrent')
            ->willReturn($dateTime);

        $this->entityManager
            ->expects(self::once())
            ->method('persist')
            ->with($newRemoteResource);

        $this->entityManager
            ->expects(self::once())
            ->method('flush');

        self::assertRemoteResourceSame(
            $newRemoteResource,
            $this->provider->store($remoteResource),
        );
    }

    public function testRemove(): void
    {
        $remoteResource = new RemoteResource(
            remoteId: 'test_image.jpg',
            type: 'image',
            url: 'https://cloudinary.com/upload/image/test_image.jpg',
            md5: 'e522f43cf89aa0afd03387c37e2b6e29',
            id: 15,
            name: 'test_image.jpg',
            size: 250,
        );

        $this->entityManager
            ->expects(self::once())
            ->method('remove')
            ->with($remoteResource);

        $this->entityManager
            ->expects(self::once())
            ->method('flush');

        $this->provider
            ->expects(self::once())
            ->method('supportsDelete')
            ->willReturn(false);

        $this->provider->remove($remoteResource);
    }

    public function testRemoveWithDelete(): void
    {
        $remoteResource = new RemoteResource(
            remoteId: 'test_image.jpg',
            type: 'image',
            url: 'https://cloudinary.com/upload/image/test_image.jpg',
            md5: 'e522f43cf89aa0afd03387c37e2b6e29',
            id: 15,
            name: 'test_image.jpg',
            size: 250,
        );

        $this->entityManager
            ->expects(self::once())
            ->method('remove')
            ->with($remoteResource);

        $this->entityManager
            ->expects(self::once())
            ->method('flush');

        $this->provider
            ->expects(self::once())
            ->method('supportsDelete')
            ->willReturn(true);

        $this->provider
            ->expects(self::once())
            ->method('deleteFromRemote')
            ->with($remoteResource);

        $this->provider->remove($remoteResource);
    }

    public function testLoadLocation(): void
    {
        $resource = new RemoteResource(
            remoteId: 'test_image.jpg',
            type: 'image',
            url: 'https://cloudinary.com/upload/image/test_image.jpg',
            md5: 'e522f43cf89aa0afd03387c37e2b6e29',
            id: 30,
            name: 'test_image.jpg',
            size: 200,
        );

        $location = new RemoteResourceLocation($resource);

        $this->locationRepository
            ->expects(self::once())
            ->method('find')
            ->with(30)
            ->willReturn($location);

        $returnedLocation = $this->provider->loadLocation(30);

        self::assertRemoteResourceLocationSame(
            $location,
            $returnedLocation,
        );
    }

    public function testLoadLocationNotFound(): void
    {
        $this->locationRepository
            ->expects(self::once())
            ->method('find')
            ->with(30)
            ->willReturn(null);

        self::expectException(RemoteResourceLocationNotFoundException::class);
        self::expectExceptionMessage('Remote resource location with ID "30" not found.');

        $this->provider->loadLocation(30);
    }

    public function testStoreLocation(): void
    {
        $resource = new RemoteResource(
            remoteId: 'test_image.jpg',
            type: 'image',
            url: 'https://cloudinary.com/upload/image/test_image.jpg',
            md5: 'e522f43cf89aa0afd03387c37e2b6e29',
            id: 30,
            name: 'test_image.jpg',
            size: 200,
        );

        $location = new RemoteResourceLocation($resource);

        $this->entityManager
            ->expects(self::once())
            ->method('persist')
            ->with($location);

        $this->entityManager
            ->expects(self::once())
            ->method('flush');

        $this->provider->storeLocation($location);
    }

    public function testRemoveLocation(): void
    {
        $resource = new RemoteResource(
            remoteId: 'test_image.jpg',
            type: 'image',
            url: 'https://cloudinary.com/upload/image/test_image.jpg',
            md5: 'e522f43cf89aa0afd03387c37e2b6e29',
            id: 30,
            name: 'test_image.jpg',
            size: 200,
        );

        $location = new RemoteResourceLocation($resource);

        $this->entityManager
            ->expects(self::once())
            ->method('remove')
            ->with($location);

        $this->entityManager
            ->expects(self::once())
            ->method('flush');

        $this->provider->removeLocation($location);
    }

    public function testLoadNamedRemoteResource(): void
    {
        $resource = new RemoteResource(
            remoteId: 'upload|image|media/images/test_image.jpg',
            type: 'image',
            url: 'https://cloudinary.com/upload/image/media/images/test_image.jpg',
            md5: 'e522f43cf89aa0afd03387c37e2b6e29',
            id: 30,
            name: 'test_image.jpg',
            size: 200,
        );

        $this->resourceRepository
            ->expects(self::once())
            ->method('findOneBy')
            ->with(['remoteId' => 'upload|image|media/images/test_image.jpg'])
            ->willReturn($resource);

        self::assertRemoteResourceSame(
            $resource,
            $this->provider->loadNamedRemoteResource('test_image'),
        );
    }

    public function testLoadNamedRemoteResourceFirstTime(): void
    {
        $resource = new RemoteResource(
            remoteId: 'upload|image|media/images/test_image2.jpg',
            type: 'image',
            url: 'https://cloudinary.com/upload/image/media/images/test_image2.jpg',
            md5: 'e522f43cf89aa0afd03387c37e2b6e29',
            id: 30,
            name: 'test_image.jpg',
            size: 200,
        );

        $this->resourceRepository
            ->expects(self::once())
            ->method('findOneBy')
            ->with(['remoteId' => 'upload|image|media/images/test_image2.jpg'])
            ->willReturn(null);

        $this->provider
            ->expects(self::once())
            ->method('loadFromRemote')
            ->with('upload|image|media/images/test_image2.jpg')
            ->willReturn($resource);

        $this->entityManager
            ->expects(self::once())
            ->method('persist')
            ->with($resource);

        $this->entityManager
            ->expects(self::once())
            ->method('flush');

        self::assertRemoteResourceSame(
            $resource,
            $this->provider->loadNamedRemoteResource('test_image2'),
        );
    }

    public function testLoadNamedRemoteResourceNotFound(): void
    {
        self::expectException(NamedRemoteResourceNotFoundException::class);
        self::expectExceptionMessage('Named remote resource with name "non_existing_image" not found.');

        $this->provider->loadNamedRemoteResource('non_existing_image');
    }

    public function testLoadExistingNamedRemoteResourceLocation(): void
    {
        $resource = new RemoteResource(
            remoteId: 'upload|image|media/images/test_image.jpg',
            type: 'image',
            url: 'https://cloudinary.com/upload/image/media/images/test_image.jpg',
            md5: 'e522f43cf89aa0afd03387c37e2b6e29',
            id: 30,
            name: 'test_image.jpg',
            size: 200,
        );

        $location = new RemoteResourceLocation($resource, 'test_image_location_source');

        $this->locationRepository
            ->expects(self::once())
            ->method('findOneBy')
            ->with(['source' => 'test_image_location_source'])
            ->willReturn($location);

        self::assertRemoteResourceLocationSame(
            $location,
            $this->provider->loadNamedRemoteResourceLocation('test_image_location'),
        );
    }

    public function testLoadExistingNamedRemoteResourceLocationWithNewWatermark(): void
    {
        $resource = new RemoteResource(
            remoteId: 'upload|image|media/images/test_image2.jpg',
            type: 'image',
            url: 'https://cloudinary.com/upload/image/media/images/test_image2.jpg',
            md5: 'e522f43cf89aa0afd03387c37e2b6e29',
            id: 30,
            name: 'test_image2.jpg',
            size: 200,
        );

        $location = new RemoteResourceLocation($resource, 'named_remote_resource_location_test_image_location2', [], 'Old watermark');

        $this->locationRepository
            ->expects(self::once())
            ->method('findOneBy')
            ->with(['source' => 'named_remote_resource_location_test_image_location2'])
            ->willReturn($location);

        $this->entityManager
            ->expects(self::once())
            ->method('persist')
            ->with($location);

        $this->entityManager
            ->expects(self::once())
            ->method('flush');

        self::assertRemoteResourceLocationSame(
            $location,
            $this->provider->loadNamedRemoteResourceLocation('test_image_location2'),
        );
    }

    public function testLoadNewNamedRemoteResourceLocationWithExistingResource(): void
    {
        $resource = new RemoteResource(
            remoteId: 'upload|image|media/images/test_image2.jpg',
            type: 'image',
            url: 'https://cloudinary.com/upload/image/media/images/test_image2.jpg',
            md5: 'e522f43cf89aa0afd03387c37e2b6e29',
            id: 30,
            name: 'test_image2.jpg',
            size: 200,
        );

        $this->locationRepository
            ->expects(self::once())
            ->method('findOneBy')
            ->with(['source' => 'named_remote_resource_location_test_image_location2'])
            ->willReturn(null);

        $this->resourceRepository
            ->expects(self::once())
            ->method('findOneBy')
            ->with(['remoteId' => 'upload|image|media/images/test_image2.jpg'])
            ->willReturn($resource);

        $location = new RemoteResourceLocation($resource, 'named_remote_resource_location_test_image_location2', [], 'This is some watermark');

        $this->entityManager
            ->expects(self::once())
            ->method('persist')
            ->with($location);

        $this->entityManager
            ->expects(self::once())
            ->method('flush');

        self::assertRemoteResourceLocationSame(
            $location,
            $this->provider->loadNamedRemoteResourceLocation('test_image_location2'),
        );
    }

    public function testLoadNewNamedRemoteResourceLocationWithNewResource(): void
    {
        $resource = new RemoteResource(
            remoteId: 'upload|image|media/images/test_image.jpg',
            type: 'image',
            url: 'https://cloudinary.com/upload/image/media/images/test_image.jpg',
            md5: 'e522f43cf89aa0afd03387c37e2b6e29',
            id: 30,
            name: 'test_image.jpg',
            size: 200,
        );

        $this->locationRepository
            ->expects(self::once())
            ->method('findOneBy')
            ->with(['source' => 'test_image_location_source'])
            ->willReturn(null);

        $this->resourceRepository
            ->expects(self::once())
            ->method('findOneBy')
            ->with(['remoteId' => 'upload|image|media/images/test_image.jpg'])
            ->willReturn(null);

        $this->provider
            ->expects(self::once())
            ->method('loadFromRemote')
            ->with('upload|image|media/images/test_image.jpg')
            ->willReturn($resource);

        $location = new RemoteResourceLocation($resource, 'test_image_location_source');

        $this->entityManager
            ->expects(self::exactly(2))
            ->method('persist')
            ->willReturnCallback(
                static fn (RemoteResource|RemoteResourceLocation $value) => match (
                    $value instanceof RemoteResourceLocation ? $value->getRemoteResource()->getRemoteId() : $value->getRemoteId()
                ) {
                    $resource->getRemoteId(), $location->getRemoteResource()->getRemoteId() => null,
                    default => throw new RuntimeException(
                        sprintf(
                            'Failed asserting that argument #1 for method "persist" with value "%s" matches one of the expecting values.',
                            $value instanceof RemoteResourceLocation ? $value->getRemoteResource()->getRemoteId() : $value->getRemoteId(),
                        ),
                    ),
                },
            );

        $this->entityManager
            ->expects(self::exactly(2))
            ->method('flush');

        self::assertRemoteResourceLocationSame(
            $location,
            $this->provider->loadNamedRemoteResourceLocation('test_image_location'),
        );
    }

    public function testLoadNewNamedRemoteResourceLocationWithNonExistingResource(): void
    {
        $this->locationRepository
            ->expects(self::once())
            ->method('findOneBy')
            ->with(['source' => 'test_image_location_source'])
            ->willReturn(null);

        $this->resourceRepository
            ->expects(self::once())
            ->method('findOneBy')
            ->with(['remoteId' => 'upload|image|media/images/test_image.jpg'])
            ->willReturn(null);

        $this->provider
            ->expects(self::once())
            ->method('loadFromRemote')
            ->with('upload|image|media/images/test_image.jpg')
            ->willThrowException(new RemoteResourceNotFoundException('upload|image|media/images/test_image.jpg'));

        self::expectException(RemoteResourceNotFoundException::class);
        self::expectExceptionMessage('Remote resource with ID "upload|image|media/images/test_image.jpg" not found.');

        $this->provider->loadNamedRemoteResourceLocation('test_image_location');
    }

    public function testLoadNamedRemoteResourceLocationNotFound(): void
    {
        self::expectException(NamedRemoteResourceLocationNotFoundException::class);
        self::expectExceptionMessage('Named remote resource location with name "non_existing_image_location" not found.');

        $this->provider->loadNamedRemoteResourceLocation('non_existing_image_location');
    }

    public function testUpload(): void
    {
        $struct = new ResourceStruct(FileStruct::fromPath('test_image.jpg'));

        $resource = new RemoteResource(
            remoteId: 'test_image.jpg',
            type: 'image',
            url: 'https://cloudinary.com/upload/image/test_image.jpg',
            md5: 'e522f43cf89aa0afd03387c37e2b6e29',
            name: 'test_image.jpg',
            size: 200,
        );

        $this->provider
            ->expects(self::once())
            ->method('internalUpload')
            ->with($struct)
            ->willReturn($resource);

        $this->resourceRepository
            ->expects(self::once())
            ->method('findOneBy')
            ->with(['remoteId' => $resource->getRemoteId()])
            ->willReturn(null);

        $this->entityManager
            ->expects(self::once())
            ->method('persist')
            ->with($resource);

        $this->entityManager
            ->expects(self::once())
            ->method('flush');

        self::assertRemoteResourceSame(
            $resource,
            $this->provider->upload($struct),
        );
    }

    public function testBuildVariationCrop(): void
    {
        $resource = new RemoteResource(
            remoteId: 'test_image.jpg',
            type: 'image',
            url: 'https://cloudinary.com/upload/image/test_image.jpg',
            md5: 'e522f43cf89aa0afd03387c37e2b6e12',
            id: 30,
            name: 'test_image.jpg',
            size: 200,
        );

        $cropSettings = [
            new CropSettings('small', 5, 10, 200, 100),
        ];

        $location = new RemoteResourceLocation($resource, null, $cropSettings);

        $cropOptions = [
            'x' => 5,
            'y' => 10,
            'width' => 200,
            'height' => 100,
            'crop' => 'crop',
        ];

        $transformations = [$cropOptions];

        $this->provider
            ->expects(self::once())
            ->method('getIdentifier')
            ->willReturn('cloudinary');

        $this->cropHandler
            ->expects(self::once())
            ->method('process')
            ->with([5, 10, 200, 100])
            ->willReturn($cropOptions);

        $variation = new RemoteResourceVariation(
            $resource,
            'https://cloudinary.com/upload/image/c_5_10_200_100/test_image.jpg',
        );

        $this->provider
            ->expects(self::once())
            ->method('internalBuildVariation')
            ->with($resource, $transformations)
            ->willReturn($variation);

        self::assertRemoteResourceVariationSame(
            $variation,
            $this->provider->buildVariation($location, 'article', 'small'),
        );
    }

    public function testBuildRawVariation(): void
    {
        $resource = new RemoteResource(
            remoteId: 'test_image.jpg',
            type: 'image',
            url: 'https://cloudinary.com/upload/image/test_image.jpg',
            md5: 'e522f43cf89aa0afd03387c37e2b6e12',
            id: 30,
            name: 'test_image.jpg',
            size: 200,
        );

        $cropOptions = [
            'x' => 5,
            'y' => 10,
            'width' => 200,
            'height' => 100,
            'crop' => 'crop',
        ];

        $transformations = [$cropOptions];

        $variation = new RemoteResourceVariation(
            $resource,
            'https://cloudinary.com/upload/image/c_5_10_200_100/test_image.jpg',
        );

        $this->provider
            ->expects(self::once())
            ->method('internalBuildVariation')
            ->with($resource, $transformations)
            ->willReturn($variation);

        self::assertRemoteResourceVariationSame(
            $variation,
            $this->provider->buildRawVariation($resource, $transformations),
        );
    }

    public function testBuildVideoThumbnail(): void
    {
        $resource = new RemoteResource(
            remoteId: 'example.mp4',
            type: 'video',
            url: 'https://cloudinary.com/upload/video/example.mp4',
            md5: 'e522f43cf89aa0afd03387c37e2b6e12',
            id: 30,
            name: 'example.mp4',
            size: 1000,
        );

        $variation = new RemoteResourceVariation(
            $resource,
            'https://cloudinary.com/upload/video/example.mp4.jpg',
        );

        $this->provider
            ->expects(self::once())
            ->method('internalBuildVideoThumbnail')
            ->with($resource, [], 15)
            ->willReturn($variation);

        self::assertRemoteResourceVariationSame(
            $variation,
            $this->provider->buildVideoThumbnail($resource, 15),
        );
    }

    public function testBuildVideoThumbnailVariation(): void
    {
        $resource = new RemoteResource(
            remoteId: 'example.mp4',
            type: 'video',
            url: 'https://cloudinary.com/upload/video/example.mp4',
            md5: 'e522f43cf89aa0afd03387c37e2b6e12',
            id: 30,
            name: 'example.mp4',
            size: 1000,
        );

        $location = new RemoteResourceLocation($resource);

        $transformations = [['fetch_format' => 'jpeg']];

        $this->provider
            ->expects(self::once())
            ->method('getIdentifier')
            ->willReturn('cloudinary');

        $this->formatHandler
            ->expects(self::once())
            ->method('process')
            ->with(['jpeg'])
            ->willReturn(['fetch_format' => 'jpeg']);

        $variation = new RemoteResourceVariation(
            $resource,
            'https://cloudinary.com/upload/video/example.mp4.jpeg',
        );

        $this->provider
            ->expects(self::once())
            ->method('internalBuildVideoThumbnail')
            ->with($resource, $transformations, 15)
            ->willReturn($variation);

        self::assertRemoteResourceVariationSame(
            $variation,
            $this->provider->buildVideoThumbnailVariation($location, 'article', 'jpeg', 15),
        );
    }

    public function testBuildVideoThumbnailRawVariation(): void
    {
        $resource = new RemoteResource(
            remoteId: 'example.mp4',
            type: 'video',
            url: 'https://cloudinary.com/upload/video/example.mp4',
            md5: 'e522f43cf89aa0afd03387c37e2b6e12',
            id: 30,
            name: 'example.mp4',
            size: 1000,
        );

        $transformations = ['fetch_format' => 'jpeg'];

        $variation = new RemoteResourceVariation(
            $resource,
            'https://cloudinary.com/upload/video/example.mp4.jpeg',
        );

        $this->provider
            ->expects(self::once())
            ->method('internalBuildVideoThumbnail')
            ->with($resource, $transformations, null)
            ->willReturn($variation);

        self::assertRemoteResourceVariationSame(
            $variation,
            $this->provider->buildVideoThumbnailRawVariation($resource, $transformations),
        );
    }

    public function testGenerateHtmlTag(): void
    {
        $resource = new RemoteResource(
            remoteId: 'image.jpg',
            type: 'image',
            url: 'https://cloudinary.com/upload/image/image.jpg',
            md5: 'e522f43cf89aa0afd03387c37e2b6e12',
            id: 30,
            name: 'image.jpg',
            size: 200,
        );

        $htmlAttributes = [
            'width' => 200,
            'height' => 100,
        ];

        $tag = '<picture><img src="https://cloudinary.com/upload/image/image.jpg" width="200" height="100"></picture>';

        $this->provider
            ->expects(self::once())
            ->method('generatePictureTag')
            ->with($resource, [], $htmlAttributes)
            ->willReturn($tag);

        self::assertSame(
            $tag,
            $this->provider->generateHtmlTag($resource, $htmlAttributes),
        );
    }

    public function testGenerateVariationHtmlTag(): void
    {
        $resource = new RemoteResource(
            remoteId: 'example.mp4',
            type: 'video',
            url: 'https://cloudinary.com/upload/video/example.mp4',
            md5: 'e522f43cf89aa0afd03387c37e2b6e12',
            id: 30,
            name: 'example.mp4',
            size: 1200,
        );

        $location = new RemoteResourceLocation($resource);

        $transformations = [['fetch_format' => 'mp4']];

        $htmlAttributes = [
            'width' => '100%',
        ];

        $tag = '<video width="100%"><source src="https://cloudinary.com/upload/video/example.mp4"></video>';

        $this->provider
            ->expects(self::once())
            ->method('getIdentifier')
            ->willReturn('cloudinary');

        $this->formatHandler
            ->expects(self::once())
            ->method('process')
            ->with(['mp4'])
            ->willReturn(['fetch_format' => 'mp4']);

        $this->provider
            ->expects(self::once())
            ->method('generateVideoTag')
            ->with($resource, $transformations, $htmlAttributes)
            ->willReturn($tag);

        self::assertSame(
            $tag,
            $this->provider->generateVariationHtmlTag($location, 'article', 'mp4', $htmlAttributes),
        );
    }

    public function testGenerateVariationHtmlTagThumbnail(): void
    {
        $resource = new RemoteResource(
            remoteId: 'example.mp4',
            type: 'video',
            url: 'https://cloudinary.com/upload/video/example.mp4',
            md5: 'e522f43cf89aa0afd03387c37e2b6e12',
            id: 30,
            name: 'example.mp4',
            size: 1200,
        );

        $location = new RemoteResourceLocation($resource);

        $transformations = [['fetch_format' => 'mp4']];

        $tag = '<video><source src="https://cloudinary.com/upload/video/example.mp4"></video>';

        $this->provider
            ->expects(self::once())
            ->method('getIdentifier')
            ->willReturn('cloudinary');

        $this->formatHandler
            ->expects(self::once())
            ->method('process')
            ->with(['mp4'])
            ->willReturn(['fetch_format' => 'mp4']);

        $this->provider
            ->expects(self::once())
            ->method('generateVideoThumbnailTag')
            ->with($resource, $transformations, [])
            ->willReturn($tag);

        self::assertSame(
            $tag,
            $this->provider->generateVariationHtmlTag($location, 'article', 'mp4', [], false, true),
        );
    }

    public function testGenerateRawVariationHtmlTagAudio(): void
    {
        $resource = new RemoteResource(
            remoteId: 'example.mp3',
            type: 'audio',
            url: 'https://cloudinary.com/upload/video/example.mp3',
            md5: 'e522f43cf89aa0afd03387c37e2b6e12',
            id: 30,
            name: 'example.mp3',
            size: 120,
        );

        $transformations = [['fetch_format' => 'mp3']];

        $htmlAttributes = [
            'width' => '100%',
        ];

        $tag = '<audio width="100%"><source src="https://cloudinary.com/upload/video/example.mp3"></audio>';

        $this->provider
            ->expects(self::once())
            ->method('generateAudioTag')
            ->with($resource, $transformations, $htmlAttributes)
            ->willReturn($tag);

        self::assertSame(
            $tag,
            $this->provider->generateRawVariationHtmlTag($resource, $transformations, $htmlAttributes),
        );
    }

    public function testGenerateRawVariationHtmlTagAudioThumbnail(): void
    {
        $resource = new RemoteResource(
            remoteId: 'example.mp3',
            type: 'audio',
            url: 'https://cloudinary.com/upload/video/example.mp3',
            md5: 'e522f43cf89aa0afd03387c37e2b6e12',
            id: 30,
            name: 'example.mp3',
            size: 120,
        );

        $transformations = [['fetch_format' => 'mp3']];

        $tag = '<audio><source src="https://cloudinary.com/upload/video/example.mp3"></audio>';

        $this->provider
            ->expects(self::once())
            ->method('generateVideoThumbnailTag')
            ->with($resource, $transformations, [])
            ->willReturn($tag);

        self::assertSame(
            $tag,
            $this->provider->generateRawVariationHtmlTag($resource, $transformations, [], false, true),
        );
    }

    public function testGenerateRawVariationHtmlTagAudioForceVideo(): void
    {
        $resource = new RemoteResource(
            remoteId: 'example.mp3',
            type: 'audio',
            url: 'https://cloudinary.com/upload/video/example.mp3',
            md5: 'e522f43cf89aa0afd03387c37e2b6e12',
            id: 30,
            name: 'example.mp3',
            size: 120,
        );

        $transformations = [['fetch_format' => 'mp3']];

        $htmlAttributes = [
            'width' => '100%',
        ];

        $tag = '<video width="100%"><source src="https://cloudinary.com/upload/video/example.mp3"></video>';

        $this->provider
            ->expects(self::once())
            ->method('generateVideoTag')
            ->with($resource, $transformations, $htmlAttributes)
            ->willReturn($tag);

        self::assertSame(
            $tag,
            $this->provider->generateRawVariationHtmlTag($resource, $transformations, $htmlAttributes, true),
        );
    }

    public function testGenerateRawVariationHtmlTagDocument(): void
    {
        $resource = new RemoteResource(
            remoteId: 'example.pdf',
            type: 'document',
            url: 'https://cloudinary.com/upload/raw/example.pdf',
            md5: 'e522f43cf89aa0afd03387c37e2b6e12',
            id: 30,
            name: 'example.pdf',
            size: 80,
        );

        $tag = '<a href="https://cloudinary.com/upload/raw/example.pdf">example.pdf</a>';

        $this->provider
            ->expects(self::once())
            ->method('generateDocumentTag')
            ->with($resource, [], [])
            ->willReturn($tag);

        self::assertSame(
            $tag,
            $this->provider->generateRawVariationHtmlTag($resource),
        );
    }

    public function testGenerateRawVariationHtmlTagOther(): void
    {
        $resource = new RemoteResource(
            remoteId: 'example.zip',
            type: 'other',
            url: 'https://cloudinary.com/upload/raw/example.zip',
            md5: 'e522f43cf89aa0afd03387c37e2b6e12',
            id: 30,
            name: 'example.zip',
            size: 30,
        );

        $htmlAttributes = ['target' => '_blank'];

        $tag = '<a href="https://cloudinary.com/upload/raw/example.zip" target="_blank">example.zip</a>';

        $this->provider
            ->expects(self::once())
            ->method('generateDownloadTag')
            ->with($resource, [], $htmlAttributes)
            ->willReturn($tag);

        self::assertSame(
            $tag,
            $this->provider->generateRawVariationHtmlTag($resource, [], $htmlAttributes),
        );
    }
}
