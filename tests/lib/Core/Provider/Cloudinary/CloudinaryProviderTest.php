<?php

declare(strict_types=1);

namespace Netgen\RemoteMedia\Tests\Core\Provider\Cloudinary;

use Doctrine\ORM\EntityManagerInterface;
use Doctrine\Persistence\ObjectRepository;
use Netgen\RemoteMedia\API\Search\Query;
use Netgen\RemoteMedia\API\Search\Result;
use Netgen\RemoteMedia\API\Upload\FileStruct;
use Netgen\RemoteMedia\API\Upload\ResourceStruct;
use Netgen\RemoteMedia\API\Values\AuthenticatedRemoteResource;
use Netgen\RemoteMedia\API\Values\AuthToken;
use Netgen\RemoteMedia\API\Values\Folder;
use Netgen\RemoteMedia\API\Values\RemoteResource;
use Netgen\RemoteMedia\API\Values\RemoteResourceLocation;
use Netgen\RemoteMedia\API\Values\RemoteResourceVariation;
use Netgen\RemoteMedia\API\Values\StatusData;
use Netgen\RemoteMedia\Core\Factory\DateTime as DateTimeFactory;
use Netgen\RemoteMedia\Core\Provider\Cloudinary\CloudinaryProvider;
use Netgen\RemoteMedia\Core\Provider\Cloudinary\CloudinaryRemoteId;
use Netgen\RemoteMedia\Core\Provider\Cloudinary\Converter\VisibilityType as VisibilityTypeConverter;
use Netgen\RemoteMedia\Core\Provider\Cloudinary\GatewayInterface;
use Netgen\RemoteMedia\Core\Provider\Cloudinary\Resolver\UploadOptions as UploadOptionsResolver;
use Netgen\RemoteMedia\Core\Resolver\Variation as VariationResolver;
use Netgen\RemoteMedia\Core\Transformation\Registry;
use Netgen\RemoteMedia\Exception\RemoteResourceNotFoundException;
use Netgen\RemoteMedia\Tests\AbstractTest;
use PHPUnit\Framework\MockObject\MockObject;
use Psr\Log\LoggerInterface;
use Symfony\Component\Mime\MimeTypesInterface;

use function count;

final class CloudinaryProviderTest extends AbstractTest
{
    protected CloudinaryProvider $cloudinaryProvider;

    /** @var \PHPUnit\Framework\MockObject\MockObject|\Netgen\RemoteMedia\Core\Provider\Cloudinary\GatewayInterface */
    protected MockObject $gateway;

    /** @var \PHPUnit\Framework\MockObject\MockObject|\Psr\Log\LoggerInterface */
    protected MockObject $logger;

    /** @var \PHPUnit\Framework\MockObject\MockObject|\Symfony\Component\Mime\MimeTypesInterface */
    protected MockObject $mimeTypes;

    protected function setUp(): void
    {
        $this->gateway = $this->createMock(GatewayInterface::class);
        $this->logger = $this->createMock(LoggerInterface::class);
        $this->mimeTypes = $this->createMock(MimeTypesInterface::class);

        $entityManager = $this->createMock(EntityManagerInterface::class);

        $entityManager
            ->expects(self::exactly(2))
            ->method('getRepository')
            ->withConsecutive([RemoteResource::class], [RemoteResourceLocation::class])
            ->willReturnOnConsecutiveCalls(
                $this->createMock(ObjectRepository::class),
                $this->createMock(ObjectRepository::class),
            );

        $this->cloudinaryProvider = new CloudinaryProvider(
            new Registry(),
            new VariationResolver(),
            $entityManager,
            $this->gateway,
            new DateTimeFactory(),
            new UploadOptionsResolver(
                new VisibilityTypeConverter(),
                ['image', 'video'],
                $this->mimeTypes,
            ),
            $this->logger,
            false,
        );
    }

    /**
     * @covers \Netgen\RemoteMedia\Core\Provider\Cloudinary\CloudinaryProvider::__construct
     * @covers \Netgen\RemoteMedia\Core\Provider\Cloudinary\CloudinaryProvider::getIdentifier
     */
    public function testIdentifier(): void
    {
        self::assertSame(
            'cloudinary',
            $this->cloudinaryProvider->getIdentifier(),
        );
    }

    /**
     * @covers \Netgen\RemoteMedia\Core\Provider\Cloudinary\CloudinaryProvider::__construct
     * @covers \Netgen\RemoteMedia\Core\Provider\Cloudinary\CloudinaryProvider::supportsFolders
     */
    public function testSupportsFolders(): void
    {
        self::assertTrue(
            $this->cloudinaryProvider->supportsFolders(),
        );
    }

    /**
     * @covers \Netgen\RemoteMedia\Core\Provider\Cloudinary\CloudinaryProvider::__construct
     * @covers \Netgen\RemoteMedia\Core\Provider\Cloudinary\CloudinaryProvider::supportsDelete
     */
    public function testSupportsDelete(): void
    {
        self::assertTrue(
            $this->cloudinaryProvider->supportsDelete(),
        );
    }

    /**
     * @covers \Netgen\RemoteMedia\Core\Provider\Cloudinary\CloudinaryProvider::__construct
     * @covers \Netgen\RemoteMedia\Core\Provider\Cloudinary\CloudinaryProvider::supportsTags
     */
    public function testSupportsTags(): void
    {
        self::assertTrue(
            $this->cloudinaryProvider->supportsTags(),
        );
    }

    /**
     * @covers \Netgen\RemoteMedia\Core\Provider\Cloudinary\CloudinaryProvider::__construct
     * @covers \Netgen\RemoteMedia\Core\Provider\Cloudinary\CloudinaryProvider::supportsProtectedResources
     */
    public function testSupportsProtectedResources(): void
    {
        $this->gateway
            ->expects(self::exactly(2))
            ->method('isEncryptionEnabled')
            ->willReturnOnConsecutiveCalls(true, false);

        self::assertTrue($this->cloudinaryProvider->supportsProtectedResources());
        self::assertFalse($this->cloudinaryProvider->supportsProtectedResources());
    }

    /**
     * @covers \Netgen\RemoteMedia\Core\Provider\Cloudinary\CloudinaryProvider::__construct
     * @covers \Netgen\RemoteMedia\Core\Provider\Cloudinary\CloudinaryProvider::status
     */
    public function testStatus(): void
    {
        $data = new StatusData([
            'bandwith' => 3245453,
            'api_limit' => 500,
            'api_limit_left' => 489,
        ]);

        $this->gateway
            ->expects(self::once())
            ->method('usage')
            ->willReturn($data);

        self::assertSame(
            $data,
            $this->cloudinaryProvider->status(),
        );
    }

    /**
     * @covers \Netgen\RemoteMedia\Core\Provider\Cloudinary\CloudinaryProvider::__construct
     * @covers \Netgen\RemoteMedia\Core\Provider\Cloudinary\CloudinaryProvider::getSupportedTypes
     */
    public function testGetSupportedTypes(): void
    {
        self::assertSame(
            RemoteResource::SUPPORTED_TYPES,
            $this->cloudinaryProvider->getSupportedTypes(),
        );
    }

    /**
     * @covers \Netgen\RemoteMedia\Core\Provider\Cloudinary\CloudinaryProvider::__construct
     * @covers \Netgen\RemoteMedia\Core\Provider\Cloudinary\CloudinaryProvider::getSupportedVisibilities
     * @covers \Netgen\RemoteMedia\Core\Provider\Cloudinary\CloudinaryProvider::supportsProtectedResources
     */
    public function testGetSupportedVisibilities(): void
    {
        $this->gateway
            ->expects(self::exactly(2))
            ->method('isEncryptionEnabled')
            ->willReturnOnConsecutiveCalls(true, false);

        self::assertSame(
            RemoteResource::SUPPORTED_VISIBILITIES,
            $this->cloudinaryProvider->getSupportedVisibilities(),
        );

        self::assertSame(
            [RemoteResource::VISIBILITY_PUBLIC],
            $this->cloudinaryProvider->getSupportedVisibilities(),
        );
    }

    /**
     * @covers \Netgen\RemoteMedia\Core\Provider\Cloudinary\CloudinaryProvider::__construct
     * @covers \Netgen\RemoteMedia\Core\Provider\Cloudinary\CloudinaryProvider::count
     */
    public function testCount(): void
    {
        $this->gateway
            ->expects(self::once())
            ->method('countResources')
            ->willReturn(4);

        self::assertSame(
            4,
            $this->cloudinaryProvider->count(),
        );
    }

    /**
     * @covers \Netgen\RemoteMedia\Core\Provider\Cloudinary\CloudinaryProvider::__construct
     * @covers \Netgen\RemoteMedia\Core\Provider\Cloudinary\CloudinaryProvider::loadFromRemote
     */
    public function testLoadFromRemote(): void
    {
        $resource = new RemoteResource([
            'remoteId' => 'upload|image|media/images/image.jpg',
            'type' => 'image',
            'url' => 'https://cloudinary.com/test/upload/images/image.jpg',
            'name' => 'image.jpg',
            'size' => 95,
            'md5' => 'e522f43cf89aa0afd03387c37e2b6e29',
        ]);

        $this->gateway
            ->expects(self::once())
            ->method('get')
            ->with(CloudinaryRemoteId::fromRemoteId('upload|image|media/images/image.jpg'))
            ->willReturn($resource);

        self::assertRemoteResourceSame(
            $resource,
            $this->cloudinaryProvider->loadFromRemote('upload|image|media/images/image.jpg'),
        );
    }

    /**
     * @covers \Netgen\RemoteMedia\Core\Provider\Cloudinary\CloudinaryProvider::__construct
     * @covers \Netgen\RemoteMedia\Core\Provider\Cloudinary\CloudinaryProvider::loadFromRemote
     */
    public function testLoadFromRemoteNotFound(): void
    {
        $this->gateway
            ->expects(self::once())
            ->method('get')
            ->with(CloudinaryRemoteId::fromRemoteId('upload|image|media/images/image2.jpg'))
            ->willThrowException(new RemoteResourceNotFoundException('upload|image|media/images/image2.jpg'));

        self::expectException(RemoteResourceNotFoundException::class);
        self::expectExceptionMessage('Remote resource with ID "upload|image|media/images/image2.jpg" not found.');

        $this->cloudinaryProvider->loadFromRemote('upload|image|media/images/image2.jpg');
    }

    /**
     * @covers \Netgen\RemoteMedia\Core\Provider\Cloudinary\CloudinaryProvider::__construct
     * @covers \Netgen\RemoteMedia\Core\Provider\Cloudinary\CloudinaryProvider::loadFromRemote
     */
    public function testLoadFromRemoteInvalidRemoteId(): void
    {
        $this->logger
            ->expects(self::once())
            ->method('notice')
            ->with('[NGRM][Cloudinary] Provided remoteId "image2.jpg" is invalid.');

        self::expectException(RemoteResourceNotFoundException::class);
        self::expectExceptionMessage('Remote resource with ID "image2.jpg" not found.');

        $this->cloudinaryProvider->loadFromRemote('image2.jpg');
    }

    /**
     * @covers \Netgen\RemoteMedia\Core\Provider\Cloudinary\CloudinaryProvider::__construct
     * @covers \Netgen\RemoteMedia\Core\Provider\Cloudinary\CloudinaryProvider::deleteFromRemote
     */
    public function testDeleteFromRemote(): void
    {
        $resource = new RemoteResource([
            'remoteId' => 'upload|image|media/images/image.jpg',
            'type' => 'image',
            'url' => 'https://cloudinary.com/test/upload/images/image.jpg',
            'name' => 'image.jpg',
            'size' => 95,
            'md5' => 'e522f43cf89aa0afd03387c37e2b6e29',
        ]);

        $this->gateway
            ->expects(self::once())
            ->method('delete')
            ->with(CloudinaryRemoteId::fromRemoteId($resource->getRemoteId()));

        $this->cloudinaryProvider->deleteFromRemote($resource);
    }

    /**
     * @covers \Netgen\RemoteMedia\Core\Provider\Cloudinary\CloudinaryProvider::__construct
     * @covers \Netgen\RemoteMedia\Core\Provider\Cloudinary\CloudinaryProvider::deleteFromRemote
     */
    public function testDeleteFromRemoteNotFound(): void
    {
        $resource = new RemoteResource([
            'remoteId' => 'upload|image|media/images/image.jpg',
            'type' => 'image',
            'url' => 'https://cloudinary.com/test/upload/images/image.jpg',
            'name' => 'image.jpg',
            'size' => 95,
            'md5' => 'e522f43cf89aa0afd03387c37e2b6e29',
        ]);

        $this->gateway
            ->expects(self::once())
            ->method('delete')
            ->with(CloudinaryRemoteId::fromRemoteId($resource->getRemoteId()))
            ->willThrowException(new RemoteResourceNotFoundException('upload|image|media/images/image.jpg'));

        self::expectException(RemoteResourceNotFoundException::class);
        self::expectExceptionMessage('Remote resource with ID "upload|image|media/images/image.jpg" not found.');

        $this->cloudinaryProvider->deleteFromRemote($resource);
    }

    /**
     * @covers \Netgen\RemoteMedia\Core\Provider\Cloudinary\CloudinaryProvider::__construct
     * @covers \Netgen\RemoteMedia\Core\Provider\Cloudinary\CloudinaryProvider::search
     */
    public function testSearch(): void
    {
        $query = new Query([
            'query' => 'test',
        ]);

        $result = new Result(
            10,
            'i4gtgoijf94fef43dss',
            [
                new RemoteResource([
                    'remoteId' => 'upload|image|media/images/image.jpg',
                    'type' => 'image',
                    'url' => 'https://cloudinary.com/test/upload/images/image.jpg',
                    'name' => 'image.jpg',
                    'size' => 95,
                    'md5' => 'e522f43cf89aa0afd03387c37e2b6e29',
                ]),
                new RemoteResource([
                    'remoteId' => 'upload|image|media/images/image2.jpg',
                    'type' => 'image',
                    'url' => 'https://cloudinary.com/test/upload/images/image2.jpg',
                    'name' => 'image2.jpg',
                    'size' => 75,
                    'md5' => 'e522f43cf89aa0afd03387c37e2b6e29',
                ]),
                new RemoteResource([
                    'remoteId' => 'upload|image|media/videos/example.mp4',
                    'type' => 'video',
                    'url' => 'https://cloudinary.com/test/upload/videos/example.mp4',
                    'name' => 'example.mp4',
                    'size' => 550,
                    'md5' => 'e522f43cf89aa0afd03387c37e2b6e29',
                ]),
            ],
        );

        $this->gateway
            ->expects(self::once())
            ->method('search')
            ->with($query)
            ->willReturn($result);

        self::assertSearchResultSame(
            $result,
            $this->cloudinaryProvider->search($query),
        );
    }

    /**
     * @covers \Netgen\RemoteMedia\Core\Provider\Cloudinary\CloudinaryProvider::__construct
     * @covers \Netgen\RemoteMedia\Core\Provider\Cloudinary\CloudinaryProvider::searchCount
     */
    public function testSearchCount(): void
    {
        $query = new Query([
            'query' => 'test',
        ]);

        $this->gateway
            ->expects(self::once())
            ->method('searchCount')
            ->with($query)
            ->willReturn(10);

        self::assertSame(
            10,
            $this->cloudinaryProvider->searchCount($query),
        );
    }

    /**
     * @covers \Netgen\RemoteMedia\Core\Provider\Cloudinary\CloudinaryProvider::__construct
     * @covers \Netgen\RemoteMedia\Core\Provider\Cloudinary\CloudinaryProvider::updateOnRemote
     */
    public function testUpdateOnRemote(): void
    {
        $resource = new RemoteResource([
            'remoteId' => 'upload|image|media/images/image.jpg',
            'type' => 'image',
            'url' => 'https://cloudinary.com/test/upload/images/image.jpg',
            'name' => 'image.jpg',
            'size' => 95,
            'tags' => ['tag1', 'tag2'],
            'altText' => 'Test alt text',
            'caption' => 'Test caption',
            'context' => [
                'source' => 'user_upload',
                'type' => 'product_image',
            ],
            'md5' => 'e522f43cf89aa0afd03387c37e2b6e29',
        ]);

        $expectedOptions = [
            'context' => [
                'alt' => $resource->getAltText(),
                'caption' => $resource->getCaption(),
                'source' => 'user_upload',
                'type' => 'product_image',
            ],
            'tags' => $resource->getTags(),
        ];

        $this->gateway
            ->expects(self::once())
            ->method('update')
            ->with(CloudinaryRemoteId::fromRemoteId($resource->getRemoteId()), $expectedOptions);

        $this->cloudinaryProvider->updateOnRemote($resource);
    }

    /**
     * @covers \Netgen\RemoteMedia\Core\Provider\Cloudinary\CloudinaryProvider::__construct
     * @covers \Netgen\RemoteMedia\Core\Provider\Cloudinary\CloudinaryProvider::updateOnRemote
     */
    public function testUpdateOnRemoteWithEmptyTags(): void
    {
        $resource = new RemoteResource([
            'remoteId' => 'upload|image|media/images/image.jpg',
            'type' => 'image',
            'url' => 'https://cloudinary.com/test/upload/images/image.jpg',
            'name' => 'image.jpg',
            'size' => 95,
            'altText' => 'Test alt text',
            'caption' => 'Test caption',
            'md5' => 'e522f43cf89aa0afd03387c37e2b6e29',
        ]);

        $expectedOptions = [
            'context' => [
                'alt' => $resource->getAltText(),
                'caption' => $resource->getCaption(),
            ],
            'tags' => $resource->getTags(),
        ];

        $this->gateway
            ->expects(self::once())
            ->method('removeAllTagsFromResource')
            ->with(CloudinaryRemoteId::fromRemoteId($resource->getRemoteId()));

        $this->gateway
            ->expects(self::once())
            ->method('update')
            ->with(CloudinaryRemoteId::fromRemoteId($resource->getRemoteId()), $expectedOptions);

        $this->cloudinaryProvider->updateOnRemote($resource);
    }

    /**
     * @covers \Netgen\RemoteMedia\Core\Provider\Cloudinary\CloudinaryProvider::__construct
     * @covers \Netgen\RemoteMedia\Core\Provider\Cloudinary\CloudinaryProvider::updateOnRemote
     */
    public function testUpdateOnRemoteNotFound(): void
    {
        $resource = new RemoteResource([
            'remoteId' => 'upload|image|media/images/image.jpg',
            'type' => 'image',
            'url' => 'https://cloudinary.com/test/upload/images/image.jpg',
            'name' => 'image.jpg',
            'size' => 95,
            'tags' => ['tag1', 'tag2'],
            'altText' => 'Test alt text',
            'caption' => 'Test caption',
            'md5' => 'e522f43cf89aa0afd03387c37e2b6e29',
        ]);

        $expectedOptions = [
            'context' => [
                'alt' => $resource->getAltText(),
                'caption' => $resource->getCaption(),
            ],
            'tags' => $resource->getTags(),
        ];

        $this->gateway
            ->expects(self::once())
            ->method('update')
            ->with(CloudinaryRemoteId::fromRemoteId($resource->getRemoteId()), $expectedOptions)
            ->willThrowException(new RemoteResourceNotFoundException($resource->getRemoteId()));

        self::expectException(RemoteResourceNotFoundException::class);
        self::expectExceptionMessage('Remote resource with ID "upload|image|media/images/image.jpg" not found.');

        $this->cloudinaryProvider->updateOnRemote($resource);
    }

    /**
     * @covers \Netgen\RemoteMedia\Core\Provider\Cloudinary\CloudinaryProvider::__construct
     * @covers \Netgen\RemoteMedia\Core\Provider\Cloudinary\CloudinaryProvider::updateOnRemote
     */
    public function testUpdateOnRemoteWithEmptyTagsNotFound(): void
    {
        $resource = new RemoteResource([
            'remoteId' => 'upload|image|media/images/image.jpg',
            'type' => 'image',
            'url' => 'https://cloudinary.com/test/upload/images/image.jpg',
            'name' => 'image.jpg',
            'size' => 95,
            'tags' => [],
            'altText' => 'Test alt text',
            'caption' => 'Test caption',
            'md5' => 'e522f43cf89aa0afd03387c37e2b6e29',
        ]);

        $this->gateway
            ->expects(self::once())
            ->method('removeAllTagsFromResource')
            ->with(CloudinaryRemoteId::fromRemoteId($resource->getRemoteId()))
            ->willThrowException(new RemoteResourceNotFoundException($resource->getRemoteId()));

        self::expectException(RemoteResourceNotFoundException::class);
        self::expectExceptionMessage('Remote resource with ID "upload|image|media/images/image.jpg" not found.');

        $this->cloudinaryProvider->updateOnRemote($resource);
    }

    /**
     * @covers \Netgen\RemoteMedia\Core\Provider\Cloudinary\CloudinaryProvider::__construct
     * @covers \Netgen\RemoteMedia\Core\Provider\Cloudinary\CloudinaryProvider::generateDownloadLink
     */
    public function testGenerateDownloadLink(): void
    {
        $resource = new RemoteResource([
            'remoteId' => 'upload|image|media/images/image.jpg',
            'type' => 'image',
            'url' => 'https://cloudinary.com/test/upload/images/image.jpg',
            'name' => 'image.jpg',
            'size' => 95,
            'md5' => 'e522f43cf89aa0afd03387c37e2b6e29',
        ]);

        $transformations = [
            'crop' => 'fit',
            'width' => 160,
            'height' => 120,
        ];

        $expectedOptions = [
            'transformation' => $transformations,
        ];

        $this->gateway
            ->expects(self::once())
            ->method('getDownloadLink')
            ->with(CloudinaryRemoteId::fromRemoteId($resource->getRemoteId()), $expectedOptions)
            ->willReturn('https://cloudinary.com/test/upload/images/image.jpg');

        self::assertSame(
            'https://cloudinary.com/test/upload/images/image.jpg',
            $this->cloudinaryProvider->generateDownloadLink($resource, $transformations),
        );
    }

    /**
     * @covers \Netgen\RemoteMedia\Core\Provider\Cloudinary\CloudinaryProvider::__construct
     * @covers \Netgen\RemoteMedia\Core\Provider\Cloudinary\CloudinaryProvider::authenticateRemoteResource
     */
    public function testAuthenticateRemoteResource(): void
    {
        $resource = new RemoteResource([
            'remoteId' => 'authenticated|image|media/images/image.jpg',
            'type' => 'image',
            'url' => 'https://cloudinary.com/test/authenticated/images/image.jpg',
            'visibility' => 'protected',
            'name' => 'image.jpg',
            'size' => 95,
            'md5' => 'e522f43cf89aa0afd03387c37e2b6e29',
        ]);

        $token = AuthToken::fromDuration(50);

        $url = 'https://cloudinary.com/test/authenticated/images/image.jpg?_token=dwejtri43t98u0vfdjf9420jre9f';

        $expectedAuthenticatedResource = new AuthenticatedRemoteResource($resource, $url, $token);

        $this->gateway
            ->expects(self::once())
            ->method('getAuthenticatedUrl')
            ->with(CloudinaryRemoteId::fromRemoteId($resource->getRemoteId()), $token)
            ->willReturn($url);

        self::assertAuthenticatedRemoteResourceSame(
            $expectedAuthenticatedResource,
            $this->cloudinaryProvider->authenticateRemoteResource($resource, $token),
        );
    }

    /**
     * @covers \Netgen\RemoteMedia\Core\Provider\Cloudinary\CloudinaryProvider::__construct
     * @covers \Netgen\RemoteMedia\Core\Provider\Cloudinary\CloudinaryProvider::authenticateRemoteResourceVariation
     */
    public function testAuthenticateRemoteResourceVariation(): void
    {
        $resource = new RemoteResource([
            'remoteId' => 'authenticated|image|media/images/image.jpg',
            'type' => 'image',
            'url' => 'https://cloudinary.com/test/authenticated/image/images/image.jpg',
            'visibility' => 'protected',
            'name' => 'image.jpg',
            'size' => 95,
            'md5' => 'e522f43cf89aa0afd03387c37e2b6e29',
        ]);

        $variationUrl = 'https://cloudinary.com/test/authenticated/image/c_120_160/q_auto/images/image.jpg';

        $transformations = [
            'crop' => 'fit',
            'width' => 160,
            'height' => 120,
        ];

        $variation = new RemoteResourceVariation($resource, $variationUrl, $transformations);

        $token = AuthToken::fromDuration(50);

        $url = 'https://cloudinary.com/test/authenticated/image/c_120_160/images/image.jpg?_token=dwejtri43t98u0vfdjf9420jre9f';

        $expectedAuthenticatedResource = new AuthenticatedRemoteResource($resource, $url, $token);

        $this->gateway
            ->expects(self::once())
            ->method('getAuthenticatedUrl')
            ->with(CloudinaryRemoteId::fromRemoteId($resource->getRemoteId()), $token, $transformations)
            ->willReturn($url);

        self::assertAuthenticatedRemoteResourceSame(
            $expectedAuthenticatedResource,
            $this->cloudinaryProvider->authenticateRemoteResourceVariation($variation, $token),
        );
    }

    /**
     * @covers \Netgen\RemoteMedia\Core\Provider\Cloudinary\CloudinaryProvider::__construct
     * @covers \Netgen\RemoteMedia\Core\Provider\Cloudinary\CloudinaryProvider::internalListFolders
     */
    public function testInternalListFolders(): void
    {
        $folders = [
            Folder::fromPath('media'),
        ];

        $this->gateway
            ->expects(self::once())
            ->method('listFolders')
            ->willReturn(['media']);

        $returnedFolders = $this->cloudinaryProvider->listFolders();

        self::assertSame(
            count($folders),
            count($returnedFolders),
        );

        foreach ($folders as $key => $folder) {
            self::assertFolderSame(
                $folder,
                $returnedFolders[$key],
            );
        }
    }

    /**
     * @covers \Netgen\RemoteMedia\Core\Provider\Cloudinary\CloudinaryProvider::__construct
     * @covers \Netgen\RemoteMedia\Core\Provider\Cloudinary\CloudinaryProvider::internalListFolders
     */
    public function testInternalListSubFolders(): void
    {
        $parent = Folder::fromPath('media');

        $folders = [
            Folder::fromPath('media/images'),
            Folder::fromPath('media/videos'),
            Folder::fromPath('media/documents'),
        ];

        $this->gateway
            ->expects(self::once())
            ->method('listSubFolders')
            ->with($parent)
            ->willReturn([
                'media/images',
                'media/videos',
                'media/documents',
            ]);

        $returnedFolders = $this->cloudinaryProvider->listFolders($parent);

        self::assertSame(
            count($folders),
            count($returnedFolders),
        );

        foreach ($folders as $key => $folder) {
            self::assertFolderSame(
                $folder,
                $returnedFolders[$key],
            );
        }
    }

    /**
     * @covers \Netgen\RemoteMedia\Core\Provider\Cloudinary\CloudinaryProvider::__construct
     * @covers \Netgen\RemoteMedia\Core\Provider\Cloudinary\CloudinaryProvider::internalCreateFolder
     */
    public function testInternalCreateFolder(): void
    {
        $this->gateway
            ->expects(self::once())
            ->method('createFolder')
            ->with('upload');

        $this->cloudinaryProvider->createFolder('upload');
    }

    /**
     * @covers \Netgen\RemoteMedia\Core\Provider\Cloudinary\CloudinaryProvider::__construct
     * @covers \Netgen\RemoteMedia\Core\Provider\Cloudinary\CloudinaryProvider::internalCreateFolder
     */
    public function testInternalCreateSubFolder(): void
    {
        $parent = Folder::fromPath('media');

        $this->gateway
            ->expects(self::once())
            ->method('createFolder')
            ->with('media/archives');

        $this->cloudinaryProvider->createFolder('archives', $parent);
    }

    /**
     * @covers \Netgen\RemoteMedia\Core\Provider\Cloudinary\CloudinaryProvider::__construct
     * @covers \Netgen\RemoteMedia\Core\Provider\Cloudinary\CloudinaryProvider::internalCountInFolder
     */
    public function testInternalCountInFolder(): void
    {
        $folder = Folder::fromPath('media/images');

        $this->gateway
            ->expects(self::once())
            ->method('countResourcesInFolder')
            ->with('media/images')
            ->willReturn(2);

        self::assertSame(
            2,
            $this->cloudinaryProvider->countInFolder($folder),
        );
    }

    /**
     * @covers \Netgen\RemoteMedia\Core\Provider\Cloudinary\CloudinaryProvider::__construct
     * @covers \Netgen\RemoteMedia\Core\Provider\Cloudinary\CloudinaryProvider::internalListTags
     */
    public function testInternalListTags(): void
    {
        $tags = [
            'tag1',
            'tag2',
            'tag3',
        ];

        $this->gateway
            ->expects(self::once())
            ->method('listTags')
            ->willReturn($tags);

        self::assertSame(
            $tags,
            $this->cloudinaryProvider->listTags(),
        );
    }

    /**
     * @covers \Netgen\RemoteMedia\Core\Provider\Cloudinary\CloudinaryProvider::__construct
     * @covers \Netgen\RemoteMedia\Core\Provider\Cloudinary\CloudinaryProvider::internalUpload
     */
    public function testInternalUpload(): void
    {
        $resource = new RemoteResource([
            'remoteId' => 'upload|image|media/images/image.jpg',
            'type' => 'image',
            'url' => 'https://cloudinary.com/test/upload/images/image.jpg',
            'name' => 'image.jpg',
            'size' => 95,
            'md5' => 'e522f43cf89aa0afd03387c37e2b6e29',
        ]);

        $folder = Folder::fromPath('upload/images');

        $options = [
            'public_id' => 'upload/images/image_new_jpg',
            'overwrite' => true,
            'invalidate' => true,
            'discard_original_filename' => true,
            'context' => [
                'alt' => '',
                'caption' => '',
                'original_filename' => 'image_new.jpg',
            ],
            'type' => 'upload',
            'resource_type' => 'image',
            'access_mode' => 'public',
            'access_control' => ['access_type' => 'anonymous'],
            'tags' => [],
        ];

        $resourceStruct = new ResourceStruct(
            FileStruct::fromUri('image.jpg'),
            'image',
            $folder,
            'public',
            'image_new.jpg',
            true,
            true,
        );

        $this->mimeTypes
            ->expects(self::once())
            ->method('guessMimeType')
            ->with($resourceStruct->getFileStruct()->getUri())
            ->willReturn('image/jpg');

        $this->gateway
            ->expects(self::once())
            ->method('upload')
            ->with(
                $resourceStruct->getFileStruct()->getUri(),
                $options,
            )
            ->willReturn($resource);

        self::assertRemoteResourceSame(
            $resource,
            $this->cloudinaryProvider->upload($resourceStruct),
        );
    }

    /**
     * @covers \Netgen\RemoteMedia\Core\Provider\Cloudinary\CloudinaryProvider::__construct
     * @covers \Netgen\RemoteMedia\Core\Provider\Cloudinary\CloudinaryProvider::internalBuildVariation
     */
    public function testInternalBuildVariation(): void
    {
        $resource = new RemoteResource([
            'remoteId' => 'upload|image|media/images/image.jpg',
            'type' => 'image',
            'url' => 'https://cloudinary.com/test/upload/images/image.jpg',
            'name' => 'image.jpg',
            'size' => 95,
            'md5' => 'e522f43cf89aa0afd03387c37e2b6e29',
        ]);

        $cropOptions = [
            'x' => 5,
            'y' => 10,
            'width' => 200,
            'height' => 100,
            'crop' => 'crop',
        ];

        $transformations = [$cropOptions];

        $url = 'https://cloudinary.com/test/c_5_10_200_100/upload/images/image.jpg';

        $variation = new RemoteResourceVariation(
            $resource,
            $url,
        );

        $this->gateway
            ->expects(self::once())
            ->method('getVariationUrl')
            ->with(
                CloudinaryRemoteId::fromRemoteId($resource->getRemoteId()),
                $transformations,
            )
            ->willReturn($url);

        self::assertRemoteResourceVariationSame(
            $variation,
            $this->cloudinaryProvider->buildRawVariation($resource, $transformations),
        );
    }

    /**
     * @covers \Netgen\RemoteMedia\Core\Provider\Cloudinary\CloudinaryProvider::__construct
     * @covers \Netgen\RemoteMedia\Core\Provider\Cloudinary\CloudinaryProvider::internalBuildVideoThumbnail
     */
    public function testGetVideoThumbnail(): void
    {
        $resource = new RemoteResource([
            'remoteId' => 'upload|image|media/videos/example.mp4',
            'type' => 'video',
            'url' => 'https://cloudinary.com/test/upload/videos/example.mp4',
            'name' => 'example.mp4',
            'size' => 495,
            'md5' => 'e522f43cf89aa0afd03387c37e2b6e29',
        ]);

        $url = 'https://cloudinary.com/test/upload/videos/example.mp4.jpg';

        $variation = new RemoteResourceVariation(
            $resource,
            $url,
        );

        $options = [
            'resource_type' => 'video',
            'transformation' => [],
            'start_offset' => 'auto',
        ];

        $this->gateway
            ->expects(self::once())
            ->method('getVideoThumbnail')
            ->with(
                CloudinaryRemoteId::fromRemoteId($resource->getRemoteId()),
                $options,
            )
            ->willReturn($url);

        self::assertRemoteResourceVariationSame(
            $variation,
            $this->cloudinaryProvider->buildVideoThumbnail($resource),
        );
    }

    /**
     * @covers \Netgen\RemoteMedia\Core\Provider\Cloudinary\CloudinaryProvider::__construct
     * @covers \Netgen\RemoteMedia\Core\Provider\Cloudinary\CloudinaryProvider::internalBuildVideoThumbnail
     */
    public function testGetVideoThumbnailAudio(): void
    {
        $resource = new RemoteResource([
            'remoteId' => 'upload|image|media/songs/example.mp3',
            'type' => 'audio',
            'url' => 'https://cloudinary.com/test/upload/songs/example.mp3',
            'name' => 'example.mp3',
            'size' => 105,
            'md5' => 'e522f43cf89aa0afd03387c37e2b6e29',
        ]);

        $url = 'https://cloudinary.com/test/upload/songs/example.mp3.jpg';

        $variation = new RemoteResourceVariation(
            $resource,
            $url,
        );

        $cropOptions = [
            'x' => 5,
            'y' => 10,
            'width' => 200,
            'height' => 100,
            'crop' => 'crop',
        ];

        $transformations = [$cropOptions];

        $options = [
            'resource_type' => 'video',
            'transformation' => $transformations,
            'raw_transformation' => 'fl_waveform',
            'start_offset' => 20,
        ];

        $this->gateway
            ->expects(self::once())
            ->method('getVideoThumbnail')
            ->with(
                CloudinaryRemoteId::fromRemoteId($resource->getRemoteId()),
                $options,
            )
            ->willReturn($url);

        self::assertRemoteResourceVariationSame(
            $variation,
            $this->cloudinaryProvider->buildVideoThumbnailRawVariation($resource, $transformations, 20),
        );
    }

    /**
     * @covers \Netgen\RemoteMedia\Core\Provider\Cloudinary\CloudinaryProvider::__construct
     * @covers \Netgen\RemoteMedia\Core\Provider\Cloudinary\CloudinaryProvider::generatePictureTag
     */
    public function testGeneratePictureTag(): void
    {
        $resource = new RemoteResource([
            'remoteId' => 'upload|image|media/images/image.jpg',
            'type' => 'image',
            'url' => 'https://cloudinary.com/test/upload/images/image.jpg',
            'name' => 'image.jpg',
            'size' => 95,
            'md5' => 'e522f43cf89aa0afd03387c37e2b6e29',
        ]);

        $options = [
            'secure' => true,
            'attributes' => [],
        ];

        $tag = '<picture><img src="https://cloudinary.com/test/upload/images/image.jpg"></picture>';

        $this->gateway
            ->expects(self::once())
            ->method('getImageTag')
            ->with(
                CloudinaryRemoteId::fromRemoteId($resource->getRemoteId()),
                $options,
            )
            ->willReturn($tag);

        self::assertSame(
            $tag,
            $this->cloudinaryProvider->generateHtmlTag($resource),
        );
    }

    /**
     * @covers \Netgen\RemoteMedia\Core\Provider\Cloudinary\CloudinaryProvider::__construct
     * @covers \Netgen\RemoteMedia\Core\Provider\Cloudinary\CloudinaryProvider::generatePictureTag
     */
    public function testGeneratePictureTagVariation(): void
    {
        $resource = new RemoteResource([
            'remoteId' => 'upload|image|media/images/image.jpg',
            'type' => 'image',
            'url' => 'https://cloudinary.com/test/upload/images/image.jpg',
            'name' => 'image.jpg',
            'size' => 95,
            'md5' => 'e522f43cf89aa0afd03387c37e2b6e29',
            'altText' => 'Alternate text',
            'caption' => 'Test title',
        ]);

        $htmlAttributes = [
            'width' => 200,
            'height' => 200,
        ];

        $cropOptions = [
            'x' => 5,
            'y' => 10,
            'width' => 200,
            'height' => 100,
            'crop' => 'crop',
        ];

        $transformations = [$cropOptions];

        $attributes = [
            'width' => 200,
            'height' => 200,
            'alt' => 'Alternate text',
            'title' => 'Test title',
        ];

        $options = [
            'secure' => true,
            'attributes' => $attributes,
            'transformation' => $transformations,
        ];

        $tag = '<picture><img alt="Alternate text" title="Test title" src="https://cloudinary.com/test/c_5_10_200_100/upload/images/image.jpg" width="200" height="200"></picture>';

        $this->gateway
            ->expects(self::once())
            ->method('getImageTag')
            ->with(
                CloudinaryRemoteId::fromRemoteId($resource->getRemoteId()),
                $options,
            )
            ->willReturn($tag);

        self::assertSame(
            $tag,
            $this->cloudinaryProvider->generateRawVariationHtmlTag($resource, $transformations, $htmlAttributes),
        );
    }

    /**
     * @covers \Netgen\RemoteMedia\Core\Provider\Cloudinary\CloudinaryProvider::__construct
     * @covers \Netgen\RemoteMedia\Core\Provider\Cloudinary\CloudinaryProvider::generateVideoTag
     */
    public function testGenerateVideoTag(): void
    {
        $resource = new RemoteResource([
            'remoteId' => 'upload|image|media/videos/example.mp4',
            'type' => 'video',
            'url' => 'https://cloudinary.com/test/upload/videos/example.mp4',
            'name' => 'example.mp4',
            'size' => 495,
            'md5' => 'e522f43cf89aa0afd03387c37e2b6e29',
        ]);

        $options = [
            'secure' => true,
            'fallback_content' => 'Your browser does not support HTML5 video tags',
            'controls' => true,
            'poster' => [
                'secure' => true,
            ],
            'attributes' => [],
        ];

        $tag = '<video><source src="https://cloudinary.com/test/upload/videos/example.mp4"></video>';

        $this->gateway
            ->expects(self::once())
            ->method('getVideoTag')
            ->with(
                CloudinaryRemoteId::fromRemoteId($resource->getRemoteId()),
                $options,
            )
            ->willReturn($tag);

        self::assertSame(
            $tag,
            $this->cloudinaryProvider->generateHtmlTag($resource),
        );
    }

    /**
     * @covers \Netgen\RemoteMedia\Core\Provider\Cloudinary\CloudinaryProvider::__construct
     * @covers \Netgen\RemoteMedia\Core\Provider\Cloudinary\CloudinaryProvider::generateVideoTag
     */
    public function testGenerateVideoTagAudio(): void
    {
        $resource = new RemoteResource([
            'remoteId' => 'upload|image|media/songs/example.mp3',
            'type' => 'audio',
            'url' => 'https://cloudinary.com/test/upload/songs/example.mp3',
            'name' => 'example.mp3',
            'size' => 105,
            'md5' => 'e522f43cf89aa0afd03387c37e2b6e29',
        ]);

        $htmlAttributes = [
            'width' => '100%',
        ];

        $options = [
            'secure' => true,
            'fallback_content' => 'Your browser does not support HTML5 video tags',
            'controls' => true,
            'poster' => [
                'secure' => true,
                'raw_transformation' => 'fl_waveform',
            ],
            'attributes' => $htmlAttributes,
        ];

        $tag = '<video width="100%"><source src="https://cloudinary.com/test/upload/songs/example.mp3"></video>';

        $this->gateway
            ->expects(self::once())
            ->method('getVideoTag')
            ->with(
                CloudinaryRemoteId::fromRemoteId($resource->getRemoteId()),
                $options,
            )
            ->willReturn($tag);

        self::assertSame(
            $tag,
            $this->cloudinaryProvider->generateRawVariationHtmlTag($resource, [], $htmlAttributes, true),
        );
    }

    /**
     * @covers \Netgen\RemoteMedia\Core\Provider\Cloudinary\CloudinaryProvider::__construct
     * @covers \Netgen\RemoteMedia\Core\Provider\Cloudinary\CloudinaryProvider::generateVideoTag
     */
    public function testGenerateVideoTagVariation(): void
    {
        $resource = new RemoteResource([
            'remoteId' => 'upload|image|media/videos/example.mp4',
            'type' => 'video',
            'url' => 'https://cloudinary.com/test/upload/videos/example.mp4',
            'name' => 'example.mp4',
            'size' => 495,
            'md5' => 'e522f43cf89aa0afd03387c37e2b6e29',
        ]);

        $cropOptions = [
            'x' => 5,
            'y' => 10,
            'width' => 200,
            'height' => 100,
            'crop' => 'crop',
        ];

        $transformations = [$cropOptions];

        $options = [
            'secure' => true,
            'fallback_content' => 'Your browser does not support HTML5 video tags',
            'controls' => true,
            'poster' => [
                'secure' => true,
                'transformation' => $transformations,
            ],
            'attributes' => [],
            'transformation' => $transformations,
        ];

        $tag = '<video><source src="https://cloudinary.com/test/c_5_10_200_100/upload/videos/example.mp4"></video>';

        $this->gateway
            ->expects(self::once())
            ->method('getVideoTag')
            ->with(
                CloudinaryRemoteId::fromRemoteId($resource->getRemoteId()),
                $options,
            )
            ->willReturn($tag);

        self::assertSame(
            $tag,
            $this->cloudinaryProvider->generateRawVariationHtmlTag($resource, $transformations),
        );
    }

    /**
     * @covers \Netgen\RemoteMedia\Core\Provider\Cloudinary\CloudinaryProvider::__construct
     * @covers \Netgen\RemoteMedia\Core\Provider\Cloudinary\CloudinaryProvider::buildVideoThumbnailRawVariation
     * @covers \Netgen\RemoteMedia\Core\Provider\Cloudinary\CloudinaryProvider::generateVideoThumbnailTag
     */
    public function testGenerateVideoTagThumbnailVariation(): void
    {
        $resource = new RemoteResource([
            'remoteId' => 'upload|image|media/videos/example.mp4',
            'type' => 'video',
            'url' => 'https://cloudinary.com/test/upload/videos/example.mp4',
            'name' => 'example.mp4',
            'size' => 495,
            'md5' => 'e522f43cf89aa0afd03387c37e2b6e29',
            'altText' => 'Alternate text',
            'caption' => 'Test caption',
        ]);

        $cropOptions = [
            'x' => 5,
            'y' => 10,
            'width' => 200,
            'height' => 100,
            'crop' => 'crop',
        ];

        $transformations = [$cropOptions];

        $options = [
            'resource_type' => 'video',
            'transformation' => $transformations,
            'start_offset' => 'auto',
        ];

        $thumbnailUrl = 'https://cloudinary.com/test/c_5_10_200_100/upload/videos/example_thumb.jpg';

        $this->gateway
            ->expects(self::once())
            ->method('getVideoThumbnail')
            ->with(
                CloudinaryRemoteId::fromRemoteId($resource->getRemoteId()),
                $options,
            )
            ->willReturn($thumbnailUrl);

        $options = [
            'secure' => true,
            'attributes' => [
                'alt' => 'Alternate text',
                'title' => 'Test caption',
            ],
            'transformation' => $transformations,
        ];

        $tag = '<picture><img alt="Alternate text" title="Test caption" src="https://cloudinary.com/test/c_5_10_200_100/upload/videos/example"></picture>';

        $this->gateway
            ->expects(self::once())
            ->method('getImageTag')
            ->with(
                CloudinaryRemoteId::fromRemoteId($resource->getRemoteId()),
                $options,
            )
            ->willReturn($tag);

        $tag = '<picture><img alt="Alternate text" title="Test caption" src="https://cloudinary.com/test/c_5_10_200_100/upload/videos/example_thumb.jpg"></picture>';

        self::assertSame(
            $tag,
            $this->cloudinaryProvider->generateRawVariationHtmlTag($resource, $transformations, [], false, true),
        );
    }

    /**
     * @covers \Netgen\RemoteMedia\Core\Provider\Cloudinary\CloudinaryProvider::__construct
     * @covers \Netgen\RemoteMedia\Core\Provider\Cloudinary\CloudinaryProvider::generateAudioTag
     */
    public function testGenerateAudioTag(): void
    {
        $resource = new RemoteResource([
            'remoteId' => 'upload|image|media/songs/example.mp3',
            'type' => 'audio',
            'url' => 'https://cloudinary.com/test/upload/songs/example.mp3',
            'name' => 'example.mp3',
            'size' => 105,
            'md5' => 'e522f43cf89aa0afd03387c37e2b6e29',
        ]);

        $htmlAttributes = [
            'width' => '100%',
        ];

        $options = [
            'secure' => true,
            'fallback_content' => 'Your browser does not support HTML5 audio tags',
            'controls' => true,
            'attributes' => $htmlAttributes,
        ];

        $tag = '<audio width="100%"><source src="https://cloudinary.com/test/upload/songs/example.mp3"></audio>';

        $this->gateway
            ->expects(self::once())
            ->method('getVideoTag')
            ->with(
                CloudinaryRemoteId::fromRemoteId($resource->getRemoteId()),
                $options,
            )
            ->willReturn($tag);

        self::assertSame(
            $tag,
            $this->cloudinaryProvider->generateRawVariationHtmlTag($resource, [], $htmlAttributes),
        );
    }

    /**
     * @covers \Netgen\RemoteMedia\Core\Provider\Cloudinary\CloudinaryProvider::__construct
     * @covers \Netgen\RemoteMedia\Core\Provider\Cloudinary\CloudinaryProvider::generateDocumentTag
     * @covers \Netgen\RemoteMedia\Core\Provider\Cloudinary\CloudinaryProvider::generateDownloadLink
     * @covers \Netgen\RemoteMedia\Core\Provider\Cloudinary\CloudinaryProvider::generateDownloadTag
     */
    public function testGenerateDocumentTag(): void
    {
        $resource = new RemoteResource([
            'remoteId' => 'upload|image|media/doc/example.pdf',
            'type' => 'document',
            'url' => 'https://cloudinary.com/test/upload/doc/example.pdf',
            'name' => 'example.pdf',
            'size' => 35,
            'md5' => 'e522f43cf89aa0afd03387c37e2b6e29',
        ]);

        $htmlAttributes = [
            'target' => '_blank',
        ];

        $url = 'https://cloudinary.com/test/upload/doc/example.pdf';

        $this->gateway
            ->expects(self::once())
            ->method('getDownloadLink')
            ->with(
                CloudinaryRemoteId::fromRemoteId($resource->getRemoteId()),
            )
            ->willReturn($url);

        self::assertSame(
            '<a href="https://cloudinary.com/test/upload/doc/example.pdf" target="_blank">example.pdf</a>',
            $this->cloudinaryProvider->generateRawVariationHtmlTag($resource, [], $htmlAttributes),
        );
    }
}
