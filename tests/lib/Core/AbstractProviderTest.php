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
use Netgen\RemoteMedia\Exception\NotSupportedException;
use Netgen\RemoteMedia\Exception\RemoteResourceLocationNotFoundException;
use Netgen\RemoteMedia\Exception\RemoteResourceNotFoundException;
use Netgen\RemoteMedia\Tests\AbstractTest;
use PHPUnit\Framework\MockObject\MockObject;
use Psr\Log\LoggerInterface;

final class AbstractProviderTest extends AbstractTest
{
    private AbstractProvider $provider;

    /** @var \PHPUnit\Framework\MockObject\MockObject|\Doctrine\ORM\EntityManagerInterface */
    private MockObject $entityManager;

    /** @var \PHPUnit\Framework\MockObject\MockObject|\Netgen\RemoteMedia\API\Factory\DateTime */
    private MockObject $dateTimeFactory;

    /** @var \PHPUnit\Framework\MockObject\MockObject|\Netgen\RemoteMedia\Core\Transformation\HandlerInterface */
    private MockObject $cropHandler;

    /** @var \PHPUnit\Framework\MockObject\MockObject|\Netgen\RemoteMedia\Core\Transformation\HandlerInterface */
    private MockObject $formatHandler;

    /** @var \PHPUnit\Framework\MockObject\MockObject|\Psr\Log\LoggerInterface */
    private MockObject $logger;

    /** @var \PHPUnit\Framework\MockObject\MockObject|\Doctrine\Persistence\ObjectRepository */
    private MockObject $resourceRepository;

    /** @var \PHPUnit\Framework\MockObject\MockObject|\Doctrine\Persistence\ObjectRepository */
    private MockObject $locationRepository;

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

        $variationResolver = new VariationResolver();
        $variationResolver->setServices($registry);
        $variationResolver->setVariations([
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
        ]);

        $this->entityManager
            ->expects(self::exactly(2))
            ->method('getRepository')
            ->withConsecutive([RemoteResource::class], [RemoteResourceLocation::class])
            ->willReturnOnConsecutiveCalls($this->resourceRepository, $this->locationRepository);

        $this->provider = $this
            ->getMockForAbstractClass(
                AbstractProvider::class,
                [
                    new Registry(),
                    $variationResolver,
                    $this->entityManager,
                    $this->dateTimeFactory,
                    $this->logger,
                    true,
                ],
            );
    }

    /**
     * @covers \Netgen\RemoteMedia\Core\AbstractProvider::__construct
     * @covers \Netgen\RemoteMedia\Core\AbstractProvider::listFolders
     */
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

    /**
     * @covers \Netgen\RemoteMedia\Core\AbstractProvider::__construct
     * @covers \Netgen\RemoteMedia\Core\AbstractProvider::listFolders
     */
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

    /**
     * @covers \Netgen\RemoteMedia\Core\AbstractProvider::__construct
     * @covers \Netgen\RemoteMedia\Core\AbstractProvider::listFolders
     */
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

    /**
     * @covers \Netgen\RemoteMedia\Core\AbstractProvider::__construct
     * @covers \Netgen\RemoteMedia\Core\AbstractProvider::createFolder
     */
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

    /**
     * @covers \Netgen\RemoteMedia\Core\AbstractProvider::__construct
     * @covers \Netgen\RemoteMedia\Core\AbstractProvider::createFolder
     */
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

    /**
     * @covers \Netgen\RemoteMedia\Core\AbstractProvider::__construct
     * @covers \Netgen\RemoteMedia\Core\AbstractProvider::createFolder
     */
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

    /**
     * @covers \Netgen\RemoteMedia\Core\AbstractProvider::__construct
     * @covers \Netgen\RemoteMedia\Core\AbstractProvider::countInFolder
     */
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

    /**
     * @covers \Netgen\RemoteMedia\Core\AbstractProvider::__construct
     * @covers \Netgen\RemoteMedia\Core\AbstractProvider::countInFolder
     */
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

    /**
     * @covers \Netgen\RemoteMedia\Core\AbstractProvider::__construct
     * @covers \Netgen\RemoteMedia\Core\AbstractProvider::listTags
     */
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

    /**
     * @covers \Netgen\RemoteMedia\Core\AbstractProvider::__construct
     * @covers \Netgen\RemoteMedia\Core\AbstractProvider::listTags
     */
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

    /**
     * @covers \Netgen\RemoteMedia\Core\AbstractProvider::__construct
     * @covers \Netgen\RemoteMedia\Core\AbstractProvider::load
     */
    public function testLoad(): void
    {
        $remoteResource = new RemoteResource([
            'id' => 15,
            'remoteId' => 'test_image.jpg',
            'url' => 'https://cloudinary.com/upload/image/test_image.jpg',
            'name' => 'test_image.jpg',
            'type' => 'image',
            'size' => 200,
            'md5' => 'e522f43cf89aa0afd03387c37e2b6e29',
        ]);

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

    /**
     * @covers \Netgen\RemoteMedia\Core\AbstractProvider::__construct
     * @covers \Netgen\RemoteMedia\Core\AbstractProvider::load
     */
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

    /**
     * @covers \Netgen\RemoteMedia\Core\AbstractProvider::__construct
     * @covers \Netgen\RemoteMedia\Core\AbstractProvider::loadByRemoteId
     */
    public function testLoadByRemoteId(): void
    {
        $remoteResource = new RemoteResource([
            'id' => 15,
            'remoteId' => 'test_image.jpg',
            'url' => 'https://cloudinary.com/upload/image/test_image.jpg',
            'name' => 'test_image.jpg',
            'type' => 'image',
            'size' => 200,
            'md5' => 'e522f43cf89aa0afd03387c37e2b6e29',
        ]);

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

    /**
     * @covers \Netgen\RemoteMedia\Core\AbstractProvider::__construct
     * @covers \Netgen\RemoteMedia\Core\AbstractProvider::loadByRemoteId
     */
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

    /**
     * @covers \Netgen\RemoteMedia\Core\AbstractProvider::__construct
     * @covers \Netgen\RemoteMedia\Core\AbstractProvider::store
     */
    public function testStoreExisting(): void
    {
        $remoteResource = new RemoteResource([
            'id' => 15,
            'remoteId' => 'test_image.jpg',
            'url' => 'https://cloudinary.com/upload/image/test_image.jpg',
            'name' => 'test_image.jpg',
            'type' => 'image',
            'size' => 200,
            'md5' => 'e522f43cf89aa0afd03387c37e2b6e29',
        ]);

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

    /**
     * @covers \Netgen\RemoteMedia\Core\AbstractProvider::__construct
     * @covers \Netgen\RemoteMedia\Core\AbstractProvider::store
     */
    public function testStoreNew(): void
    {
        $remoteResource = new RemoteResource([
            'remoteId' => 'test_image.jpg',
            'url' => 'https://cloudinary.com/upload/image/test_image.jpg',
            'name' => 'test_image.jpg',
            'type' => 'image',
            'size' => 200,
            'md5' => 'e522f43cf89aa0afd03387c37e2b6e29',
        ]);

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

    /**
     * @covers \Netgen\RemoteMedia\Core\AbstractProvider::__construct
     * @covers \Netgen\RemoteMedia\Core\AbstractProvider::store
     */
    public function testStoreExistingRemoteId(): void
    {
        $oldRemoteResource = new RemoteResource([
            'id' => 15,
            'remoteId' => 'test_image.jpg',
            'url' => 'https://cloudinary.com/upload/image/test_image.jpg',
            'name' => 'test_image.jpg',
            'type' => 'image',
            'size' => 200,
            'md5' => 'e522f43cf89aa0afd03387c37e2b6e29',
        ]);

        $remoteResource = new RemoteResource([
            'remoteId' => 'test_image.jpg',
            'url' => 'https://cloudinary.com/upload/image/test_image_2.jpg',
            'name' => 'test_image.jpg',
            'type' => 'image',
            'size' => 250,
            'md5' => 'e522f43cf89aa0afd03387c37e2b6e29',
        ]);

        $dateTime = new DateTimeImmutable('now');

        $newRemoteResource = new RemoteResource([
            'id' => 15,
            'remoteId' => 'test_image.jpg',
            'url' => 'https://cloudinary.com/upload/image/test_image_2.jpg',
            'name' => 'test_image.jpg',
            'type' => 'image',
            'size' => 250,
            'updatedAt' => $dateTime,
            'md5' => 'e522f43cf89aa0afd03387c37e2b6e29',
        ]);

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

    /**
     * @covers \Netgen\RemoteMedia\Core\AbstractProvider::__construct
     * @covers \Netgen\RemoteMedia\Core\AbstractProvider::remove
     */
    public function testRemove(): void
    {
        $remoteResource = new RemoteResource([
            'id' => 15,
            'remoteId' => 'test_image.jpg',
            'url' => 'https://cloudinary.com/upload/image/test_image.jpg',
            'name' => 'test_image.jpg',
            'type' => 'image',
            'size' => 250,
            'md5' => 'e522f43cf89aa0afd03387c37e2b6e29',
        ]);

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

    /**
     * @covers \Netgen\RemoteMedia\Core\AbstractProvider::__construct
     * @covers \Netgen\RemoteMedia\Core\AbstractProvider::remove
     */
    public function testRemoveWithDelete(): void
    {
        $remoteResource = new RemoteResource([
            'id' => 15,
            'remoteId' => 'test_image.jpg',
            'url' => 'https://cloudinary.com/upload/image/test_image.jpg',
            'name' => 'test_image.jpg',
            'type' => 'image',
            'size' => 250,
            'md5' => 'e522f43cf89aa0afd03387c37e2b6e29',
        ]);

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

    /**
     * @covers \Netgen\RemoteMedia\Core\AbstractProvider::__construct
     * @covers \Netgen\RemoteMedia\Core\AbstractProvider::loadLocation
     */
    public function testLoadLocation(): void
    {
        $resource = new RemoteResource([
            'id' => 30,
            'remoteId' => 'test_image.jpg',
            'url' => 'https://cloudinary.com/upload/image/test_image.jpg',
            'name' => 'test_image.jpg',
            'type' => 'image',
            'size' => 200,
            'md5' => 'e522f43cf89aa0afd03387c37e2b6e29',
        ]);

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

    /**
     * @covers \Netgen\RemoteMedia\Core\AbstractProvider::__construct
     * @covers \Netgen\RemoteMedia\Core\AbstractProvider::loadLocation
     */
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

    /**
     * @covers \Netgen\RemoteMedia\Core\AbstractProvider::__construct
     * @covers \Netgen\RemoteMedia\Core\AbstractProvider::storeLocation
     */
    public function testStoreLocation(): void
    {
        $resource = new RemoteResource([
            'id' => 30,
            'remoteId' => 'test_image.jpg',
            'url' => 'https://cloudinary.com/upload/image/test_image.jpg',
            'name' => 'test_image.jpg',
            'type' => 'image',
            'size' => 200,
            'md5' => 'e522f43cf89aa0afd03387c37e2b6e29',
        ]);

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

    /**
     * @covers \Netgen\RemoteMedia\Core\AbstractProvider::__construct
     * @covers \Netgen\RemoteMedia\Core\AbstractProvider::removeLocation
     */
    public function testRemoveLocation(): void
    {
        $resource = new RemoteResource([
            'id' => 30,
            'remoteId' => 'test_image.jpg',
            'url' => 'https://cloudinary.com/upload/image/test_image.jpg',
            'name' => 'test_image.jpg',
            'type' => 'image',
            'size' => 200,
            'md5' => 'e522f43cf89aa0afd03387c37e2b6e29',
        ]);

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

    /**
     * @covers \Netgen\RemoteMedia\Core\AbstractProvider::__construct
     * @covers \Netgen\RemoteMedia\Core\AbstractProvider::store
     * @covers \Netgen\RemoteMedia\Core\AbstractProvider::upload
     */
    public function testUpload(): void
    {
        $struct = new ResourceStruct(FileStruct::fromUri('test_image.jpg'));

        $resource = new RemoteResource([
            'remoteId' => 'test_image.jpg',
            'url' => 'https://cloudinary.com/upload/image/test_image.jpg',
            'name' => 'test_image.jpg',
            'type' => 'image',
            'size' => 200,
            'md5' => 'e522f43cf89aa0afd03387c37e2b6e29',
        ]);

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

    /**
     * @covers \Netgen\RemoteMedia\Core\AbstractProvider::__construct
     * @covers \Netgen\RemoteMedia\Core\AbstractProvider::buildVariation
     */
    public function testBuildVariationCrop(): void
    {
        $resource = new RemoteResource([
            'id' => 30,
            'remoteId' => 'test_image.jpg',
            'url' => 'https://cloudinary.com/upload/image/test_image.jpg',
            'name' => 'test_image.jpg',
            'type' => 'image',
            'size' => 200,
            'md5' => 'e522f43cf89aa0afd03387c37e2b6e12',
        ]);

        $cropSettings = [
            new CropSettings('small', 5, 10, 200, 100),
        ];

        $location = new RemoteResourceLocation($resource, $cropSettings);

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

    /**
     * @covers \Netgen\RemoteMedia\Core\AbstractProvider::__construct
     * @covers \Netgen\RemoteMedia\Core\AbstractProvider::buildRawVariation
     */
    public function testBuildRawVariation(): void
    {
        $resource = new RemoteResource([
            'id' => 30,
            'remoteId' => 'test_image.jpg',
            'url' => 'https://cloudinary.com/upload/image/test_image.jpg',
            'name' => 'test_image.jpg',
            'type' => 'image',
            'size' => 200,
            'md5' => 'e522f43cf89aa0afd03387c37e2b6e12',
        ]);

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

    /**
     * @covers \Netgen\RemoteMedia\Core\AbstractProvider::__construct
     * @covers \Netgen\RemoteMedia\Core\AbstractProvider::buildVideoThumbnail
     */
    public function testBuildVideoThumbnail(): void
    {
        $resource = new RemoteResource([
            'id' => 30,
            'remoteId' => 'example.mp4',
            'url' => 'https://cloudinary.com/upload/video/example.mp4',
            'name' => 'example.mp4',
            'type' => 'video',
            'size' => 1000,
            'md5' => 'e522f43cf89aa0afd03387c37e2b6e12',
        ]);

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

    /**
     * @covers \Netgen\RemoteMedia\Core\AbstractProvider::__construct
     * @covers \Netgen\RemoteMedia\Core\AbstractProvider::buildVideoThumbnailVariation
     */
    public function testBuildVideoThumbnailVariation(): void
    {
        $resource = new RemoteResource([
            'id' => 30,
            'remoteId' => 'example.mp4',
            'url' => 'https://cloudinary.com/upload/video/example.mp4',
            'name' => 'example.mp4',
            'type' => 'video',
            'size' => 1000,
            'md5' => 'e522f43cf89aa0afd03387c37e2b6e12',
        ]);

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

    /**
     * @covers \Netgen\RemoteMedia\Core\AbstractProvider::__construct
     * @covers \Netgen\RemoteMedia\Core\AbstractProvider::buildVideoThumbnailRawVariation
     */
    public function testBuildVideoThumbnailRawVariation(): void
    {
        $resource = new RemoteResource([
            'id' => 30,
            'remoteId' => 'example.mp4',
            'url' => 'https://cloudinary.com/upload/video/example.mp4',
            'name' => 'example.mp4',
            'type' => 'video',
            'size' => 1000,
            'md5' => 'e522f43cf89aa0afd03387c37e2b6e12',
        ]);

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

    /**
     * @covers \Netgen\RemoteMedia\Core\AbstractProvider::__construct
     * @covers \Netgen\RemoteMedia\Core\AbstractProvider::generateHtmlTag
     * @covers \Netgen\RemoteMedia\Core\AbstractProvider::generateRawVariationHtmlTag
     */
    public function testGenerateHtmlTag(): void
    {
        $resource = new RemoteResource([
            'id' => 30,
            'remoteId' => 'image.jpg',
            'url' => 'https://cloudinary.com/upload/image/image.jpg',
            'name' => 'image.jpg',
            'type' => 'image',
            'size' => 200,
            'md5' => 'e522f43cf89aa0afd03387c37e2b6e12',
        ]);

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

    /**
     * @covers \Netgen\RemoteMedia\Core\AbstractProvider::__construct
     * @covers \Netgen\RemoteMedia\Core\AbstractProvider::generateRawVariationHtmlTag
     * @covers \Netgen\RemoteMedia\Core\AbstractProvider::generateVariationHtmlTag
     */
    public function testGenerateVariationHtmlTag(): void
    {
        $resource = new RemoteResource([
            'id' => 30,
            'remoteId' => 'example.mp4',
            'url' => 'https://cloudinary.com/upload/video/example.mp4',
            'name' => 'example.mp4',
            'type' => 'video',
            'size' => 1200,
            'md5' => 'e522f43cf89aa0afd03387c37e2b6e12',
        ]);

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

    /**
     * @covers \Netgen\RemoteMedia\Core\AbstractProvider::__construct
     * @covers \Netgen\RemoteMedia\Core\AbstractProvider::generateRawVariationHtmlTag
     * @covers \Netgen\RemoteMedia\Core\AbstractProvider::generateVariationHtmlTag
     */
    public function testGenerateVariationHtmlTagThumbnail(): void
    {
        $resource = new RemoteResource([
            'id' => 30,
            'remoteId' => 'example.mp4',
            'url' => 'https://cloudinary.com/upload/video/example.mp4',
            'name' => 'example.mp4',
            'type' => 'video',
            'size' => 1200,
            'md5' => 'e522f43cf89aa0afd03387c37e2b6e12',
        ]);

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

    /**
     * @covers \Netgen\RemoteMedia\Core\AbstractProvider::__construct
     * @covers \Netgen\RemoteMedia\Core\AbstractProvider::generateRawVariationHtmlTag
     */
    public function testGenerateRawVariationHtmlTagAudio(): void
    {
        $resource = new RemoteResource([
            'id' => 30,
            'remoteId' => 'example.mp3',
            'url' => 'https://cloudinary.com/upload/video/example.mp3',
            'name' => 'example.mp3',
            'type' => 'audio',
            'size' => 120,
            'md5' => 'e522f43cf89aa0afd03387c37e2b6e12',
        ]);

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

    /**
     * @covers \Netgen\RemoteMedia\Core\AbstractProvider::__construct
     * @covers \Netgen\RemoteMedia\Core\AbstractProvider::generateRawVariationHtmlTag
     */
    public function testGenerateRawVariationHtmlTagAudioThumbnail(): void
    {
        $resource = new RemoteResource([
            'id' => 30,
            'remoteId' => 'example.mp3',
            'url' => 'https://cloudinary.com/upload/video/example.mp3',
            'name' => 'example.mp3',
            'type' => 'audio',
            'size' => 120,
            'md5' => 'e522f43cf89aa0afd03387c37e2b6e12',
        ]);

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

    /**
     * @covers \Netgen\RemoteMedia\Core\AbstractProvider::__construct
     * @covers \Netgen\RemoteMedia\Core\AbstractProvider::generateRawVariationHtmlTag
     */
    public function testGenerateRawVariationHtmlTagAudioForceVideo(): void
    {
        $resource = new RemoteResource([
            'id' => 30,
            'remoteId' => 'example.mp3',
            'url' => 'https://cloudinary.com/upload/video/example.mp3',
            'name' => 'example.mp3',
            'type' => 'audio',
            'size' => 120,
            'md5' => 'e522f43cf89aa0afd03387c37e2b6e12',
        ]);

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

    /**
     * @covers \Netgen\RemoteMedia\Core\AbstractProvider::__construct
     * @covers \Netgen\RemoteMedia\Core\AbstractProvider::generateRawVariationHtmlTag
     */
    public function testGenerateRawVariationHtmlTagDocument(): void
    {
        $resource = new RemoteResource([
            'id' => 30,
            'remoteId' => 'example.pdf',
            'url' => 'https://cloudinary.com/upload/raw/example.pdf',
            'name' => 'example.pdf',
            'type' => 'document',
            'size' => 80,
            'md5' => 'e522f43cf89aa0afd03387c37e2b6e12',
        ]);

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

    /**
     * @covers \Netgen\RemoteMedia\Core\AbstractProvider::__construct
     * @covers \Netgen\RemoteMedia\Core\AbstractProvider::generateRawVariationHtmlTag
     */
    public function testGenerateRawVariationHtmlTagOther(): void
    {
        $resource = new RemoteResource([
            'id' => 30,
            'remoteId' => 'example.zip',
            'url' => 'https://cloudinary.com/upload/raw/example.zip',
            'name' => 'example.zip',
            'type' => 'other',
            'size' => 30,
            'md5' => 'e522f43cf89aa0afd03387c37e2b6e12',
        ]);

        $htmlAttributes = ['target' => '_blank'];

        $tag = '<a href="https://cloudinary.com/upload/raw/example.zip" target="_blank">example.zip</a>';

        $this->provider
            ->expects(self::once())
            ->method('generateDownloadTag')
            ->with($resource, $htmlAttributes)
            ->willReturn($tag);

        self::assertSame(
            $tag,
            $this->provider->generateRawVariationHtmlTag($resource, [], $htmlAttributes),
        );
    }
}
