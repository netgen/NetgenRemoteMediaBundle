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
use Netgen\RemoteMedia\Tests\AbstractTestCase;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\MockObject\MockObject;
use Psr\Log\LoggerInterface;
use Psr\Log\NullLogger;
use Symfony\Component\Mime\MimeTypesInterface;

use function count;
use function sprintf;

#[CoversClass(CloudinaryProvider::class)]
final class CloudinaryProviderTest extends AbstractTestCase
{
    protected CloudinaryProvider $cloudinaryProvider;

    protected MockObject|GatewayInterface $gateway;

    protected MockObject|LoggerInterface $logger;

    protected MockObject|MimeTypesInterface $mimeTypes;

    protected function setUp(): void
    {
        $this->gateway = $this->createMock(GatewayInterface::class);
        $this->logger = $this->createMock(LoggerInterface::class);
        $this->mimeTypes = $this->createMock(MimeTypesInterface::class);

        $entityManager = $this->createMock(EntityManagerInterface::class);

        $entityManager
            ->expects(self::exactly(2))
            ->method('getRepository')
            ->willReturnMap(
                [
                    [RemoteResource::class, $this->createMock(ObjectRepository::class)],
                    [RemoteResourceLocation::class, $this->createMock(ObjectRepository::class)],
                ],
            );

        $this->cloudinaryProvider = new CloudinaryProvider(
            new Registry(),
            new VariationResolver(
                new Registry(),
                new NullLogger(),
            ),
            $entityManager,
            $this->gateway,
            new DateTimeFactory(),
            new UploadOptionsResolver(
                new VisibilityTypeConverter(),
                ['image', 'video'],
                $this->mimeTypes,
            ),
            [],
            [],
            $this->logger,
            false,
        );
    }

    public function testIdentifier(): void
    {
        self::assertSame(
            'cloudinary',
            $this->cloudinaryProvider->getIdentifier(),
        );
    }

    public function testSupportsFolders(): void
    {
        self::assertTrue(
            $this->cloudinaryProvider->supportsFolders(),
        );
    }

    public function testSupportsDelete(): void
    {
        self::assertTrue(
            $this->cloudinaryProvider->supportsDelete(),
        );
    }

    public function testSupportsTags(): void
    {
        self::assertTrue(
            $this->cloudinaryProvider->supportsTags(),
        );
    }

    public function testSupportsProtectedResources(): void
    {
        $this->gateway
            ->expects(self::exactly(2))
            ->method('isEncryptionEnabled')
            ->willReturnOnConsecutiveCalls(true, false);

        self::assertTrue($this->cloudinaryProvider->supportsProtectedResources());
        self::assertFalse($this->cloudinaryProvider->supportsProtectedResources());
    }

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

    public function testGetSupportedTypes(): void
    {
        self::assertSame(
            RemoteResource::SUPPORTED_TYPES,
            $this->cloudinaryProvider->getSupportedTypes(),
        );
    }

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

    public function testLoadFromRemote(): void
    {
        $resource = new RemoteResource(
            remoteId: 'upload|image|media/images/image.jpg',
            type: 'image',
            url: 'https://cloudinary.com/test/upload/images/image.jpg',
            md5: 'e522f43cf89aa0afd03387c37e2b6e29',
            name: 'image.jpg',
            size: 95,
        );

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

    public function testDeleteFromRemote(): void
    {
        $resource = new RemoteResource(
            remoteId: 'upload|image|media/images/image.jpg',
            type: 'image',
            url: 'https://cloudinary.com/test/upload/images/image.jpg',
            md5: 'e522f43cf89aa0afd03387c37e2b6e29',
            name: 'image.jpg',
            size: 95,
        );

        $this->gateway
            ->expects(self::once())
            ->method('delete')
            ->with(CloudinaryRemoteId::fromRemoteId($resource->getRemoteId()));

        $this->cloudinaryProvider->deleteFromRemote($resource);
    }

    public function testDeleteFromRemoteNotFound(): void
    {
        $resource = new RemoteResource(
            remoteId: 'upload|image|media/images/image.jpg',
            type: 'image',
            url: 'https://cloudinary.com/test/upload/images/image.jpg',
            md5: 'e522f43cf89aa0afd03387c37e2b6e29',
            name: 'image.jpg',
            size: 95,
        );

        $this->gateway
            ->expects(self::once())
            ->method('delete')
            ->with(CloudinaryRemoteId::fromRemoteId($resource->getRemoteId()))
            ->willThrowException(new RemoteResourceNotFoundException('upload|image|media/images/image.jpg'));

        self::expectException(RemoteResourceNotFoundException::class);
        self::expectExceptionMessage('Remote resource with ID "upload|image|media/images/image.jpg" not found.');

        $this->cloudinaryProvider->deleteFromRemote($resource);
    }

    public function testSearch(): void
    {
        $query = new Query(
            query: 'test',
        );

        $result = new Result(
            10,
            'i4gtgoijf94fef43dss',
            [
                new RemoteResource(
                    remoteId: 'upload|image|media/images/image.jpg',
                    type: 'image',
                    url: 'https://cloudinary.com/test/upload/images/image.jpg',
                    md5: 'e522f43cf89aa0afd03387c37e2b6e29',
                    name: 'image.jpg',
                    size: 95,
                ),
                new RemoteResource(
                    remoteId: 'upload|image|media/images/image2.jpg',
                    type: 'image',
                    url: 'https://cloudinary.com/test/upload/images/image2.jpg',
                    md5: 'e522f43cf89aa0afd03387c37e2b6e29',
                    name: 'image2.jpg',
                    size: 75,
                ),
                new RemoteResource(
                    remoteId: 'upload|image|media/videos/example.mp4',
                    type: 'video',
                    url: 'https://cloudinary.com/test/upload/videos/example.mp4',
                    md5: 'e522f43cf89aa0afd03387c37e2b6e29',
                    name: 'example.mp4',
                    size: 550,
                ),
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

    public function testSearchCount(): void
    {
        $query = new Query(
            query: 'test',
        );

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

    public function testUpdateOnRemote(): void
    {
        $resource = new RemoteResource(
            remoteId: 'upload|image|media/images/image.jpg',
            type: 'image',
            url: 'https://cloudinary.com/test/upload/images/image.jpg',
            md5: 'e522f43cf89aa0afd03387c37e2b6e29',
            name: 'image.jpg',
            size: 95,
            altText: 'Test alt text',
            caption: 'Test caption',
            tags: ['tag1', 'tag2'],
            context: [
                'source' => 'user_upload',
                'type' => 'product_image',
            ],
        );

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

    public function testUpdateOnRemoteWithEmptyTags(): void
    {
        $resource = new RemoteResource(
            remoteId: 'upload|image|media/images/image.jpg',
            type: 'image',
            url: 'https://cloudinary.com/test/upload/images/image.jpg',
            md5: 'e522f43cf89aa0afd03387c37e2b6e29',
            name: 'image.jpg',
            size: 95,
            altText: 'Test alt text',
            caption: 'Test caption',
        );

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

    public function testUpdateOnRemoteNotFound(): void
    {
        $resource = new RemoteResource(
            remoteId: 'upload|image|media/images/image.jpg',
            type: 'image',
            url: 'https://cloudinary.com/test/upload/images/image.jpg',
            md5: 'e522f43cf89aa0afd03387c37e2b6e29',
            name: 'image.jpg',
            size: 95,
            altText: 'Test alt text',
            caption: 'Test caption',
            tags: ['tag1', 'tag2'],
        );

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

    public function testUpdateOnRemoteWithEmptyTagsNotFound(): void
    {
        $resource = new RemoteResource(
            remoteId: 'upload|image|media/images/image.jpg',
            type: 'image',
            url: 'https://cloudinary.com/test/upload/images/image.jpg',
            md5: 'e522f43cf89aa0afd03387c37e2b6e29',
            name: 'image.jpg',
            size: 95,
            altText: 'Test alt text',
            caption: 'Test caption',
            tags: [],
        );

        $this->gateway
            ->expects(self::once())
            ->method('removeAllTagsFromResource')
            ->with(CloudinaryRemoteId::fromRemoteId($resource->getRemoteId()))
            ->willThrowException(new RemoteResourceNotFoundException($resource->getRemoteId()));

        self::expectException(RemoteResourceNotFoundException::class);
        self::expectExceptionMessage('Remote resource with ID "upload|image|media/images/image.jpg" not found.');

        $this->cloudinaryProvider->updateOnRemote($resource);
    }

    public function testGenerateDownloadLink(): void
    {
        $resource = new RemoteResource(
            remoteId: 'upload|image|media/images/image.jpg',
            type: 'image',
            url: 'https://cloudinary.com/test/upload/images/image.jpg',
            md5: 'e522f43cf89aa0afd03387c37e2b6e29',
            name: 'image.jpg',
            size: 95,
        );

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

    public function testGenerateDownloadLinkAuthenticated(): void
    {
        $resource = new AuthenticatedRemoteResource(
            remoteResource: new RemoteResource(
                remoteId: 'upload|image|media/images/image.jpg',
                type: 'image',
                url: 'https://cloudinary.com/test/upload/images/image.jpg',
                md5: 'e522f43cf89aa0afd03387c37e2b6e29',
                name: 'image.jpg',
                size: 95,
            ),
            url: 'https://cloudinary.com/test/upload/images/image.jpg?token=tds56znwfgi42dwew',
            token: AuthToken::fromDuration(60),
        );

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
            ->willReturn('https://cloudinary.com/test/upload/images/image.jpg?token=tds56znwfgi42dwew');

        self::assertSame(
            'https://cloudinary.com/test/upload/images/image.jpg?token=tds56znwfgi42dwew',
            $this->cloudinaryProvider->generateDownloadLink($resource, $transformations),
        );
    }

    public function testAuthenticateRemoteResource(): void
    {
        $resource = new RemoteResource(
            remoteId: 'authenticated|image|media/images/image.jpg',
            type: 'image',
            url: 'https://cloudinary.com/test/authenticated/images/image.jpg',
            md5: 'e522f43cf89aa0afd03387c37e2b6e29',
            name: 'image.jpg',
            visibility: 'protected',
            size: 95,
        );

        $token = AuthToken::fromDuration(50);

        $url = 'https://cloudinary.com/test/authenticated/images/image.jpg?_token=dwejtri43t98u0vfdjf9420jre9f';

        $expectedAuthenticatedResource = new AuthenticatedRemoteResource($resource, $url, $token);

        $this->gateway
            ->expects(self::once())
            ->method('getAuthenticatedUrl')
            ->with(CloudinaryRemoteId::fromRemoteId($resource->getRemoteId()), $token)
            ->willReturn($url);

        self::assertRemoteResourceSame(
            $expectedAuthenticatedResource,
            $this->cloudinaryProvider->authenticateRemoteResource($resource, $token),
        );
    }

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

        self::assertCount(
            count($folders),
            $returnedFolders,
        );

        foreach ($folders as $key => $folder) {
            self::assertFolderSame(
                $folder,
                $returnedFolders[$key],
            );
        }
    }

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

        self::assertCount(
            count($folders),
            $returnedFolders,
        );

        foreach ($folders as $key => $folder) {
            self::assertFolderSame(
                $folder,
                $returnedFolders[$key],
            );
        }
    }

    public function testInternalCreateFolder(): void
    {
        $this->gateway
            ->expects(self::once())
            ->method('createFolder')
            ->with('upload');

        $this->cloudinaryProvider->createFolder('upload');
    }

    public function testInternalCreateSubFolder(): void
    {
        $parent = Folder::fromPath('media');

        $this->gateway
            ->expects(self::once())
            ->method('createFolder')
            ->with('media/archives');

        $this->cloudinaryProvider->createFolder('archives', $parent);
    }

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

    public function testInternalUpload(): void
    {
        $resource = new RemoteResource(
            remoteId: 'upload|image|media/images/image.jpg',
            type: 'image',
            url: 'https://cloudinary.com/test/upload/images/image.jpg',
            md5: 'e522f43cf89aa0afd03387c37e2b6e29',
            name: 'image.jpg',
            size: 95,
        );

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
            FileStruct::fromPath('image.jpg'),
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

    public function testInternalBuildVariation(): void
    {
        $resource = new RemoteResource(
            remoteId: 'upload|image|media/images/image.jpg',
            type: 'image',
            url: 'https://cloudinary.com/test/upload/images/image.jpg',
            md5: 'e522f43cf89aa0afd03387c37e2b6e29',
            name: 'image.jpg',
            size: 95,
        );

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

    public function testGetVideoThumbnail(): void
    {
        $resource = new RemoteResource(
            remoteId: 'upload|image|media/videos/example.mp4',
            type: 'video',
            url: 'https://cloudinary.com/test/upload/videos/example.mp4',
            md5: 'e522f43cf89aa0afd03387c37e2b6e29',
            name: 'example.mp4',
            size: 495,
        );

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

    public function testGetVideoThumbnailAuthenticated(): void
    {
        $url = 'https://cloudinary.com/test/upload/videos/example.mp4.jpg?token=7cd6988913bc2d89ea8899b72f3ab4b9';
        $token = AuthToken::fromDuration(60);

        $resource = new AuthenticatedRemoteResource(
            remoteResource: new RemoteResource(
                remoteId: 'upload|image|media/videos/example.mp4',
                type: 'video',
                url: 'https://cloudinary.com/test/upload/videos/example.mp4',
                md5: 'e522f43cf89aa0afd03387c37e2b6e29',
                name: 'example.mp4',
                size: 495,
            ),
            url: $url,
            token: $token,
        );

        $variation = new RemoteResourceVariation($resource, $url);

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
                $token,
            )
            ->willReturn($url);

        self::assertRemoteResourceVariationSame(
            $variation,
            $this->cloudinaryProvider->buildVideoThumbnail($resource),
        );
    }

    public function testGetVideoThumbnailAudio(): void
    {
        $resource = new RemoteResource(
            remoteId: 'upload|image|media/songs/example.mp3',
            type: 'audio',
            url: 'https://cloudinary.com/test/upload/songs/example.mp3',
            md5: 'e522f43cf89aa0afd03387c37e2b6e29',
            name: 'example.mp3',
            size: 105,
        );

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

    public function testGeneratePictureTag(): void
    {
        $resource = new RemoteResource(
            remoteId: 'upload|image|media/images/image.jpg',
            type: 'image',
            url: 'https://cloudinary.com/test/upload/images/image.jpg',
            md5: 'e522f43cf89aa0afd03387c37e2b6e29',
            name: 'image.jpg',
            size: 95,
        );

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

    public function testGeneratePictureTagAuthenticated(): void
    {
        $url = 'https://cloudinary.com/test/upload/images/image.jpg?token=d44bca9f0a8b02dd023863f2d577d17a';
        $token = AuthToken::fromDuration(60);

        $resource = new AuthenticatedRemoteResource(
            remoteResource: new RemoteResource(
                remoteId: 'upload|image|media/images/image.jpg',
                type: 'image',
                url: 'https://cloudinary.com/test/upload/images/image.jpg',
                md5: 'e522f43cf89aa0afd03387c37e2b6e29',
                name: 'image.jpg',
                size: 95,
            ),
            url: $url,
            token: $token,
        );

        $options = [
            'secure' => true,
            'attributes' => [],
        ];

        $tag = sprintf('<picture><img src="%s"></picture>', $url);

        $this->gateway
            ->expects(self::once())
            ->method('getImageTag')
            ->with(
                CloudinaryRemoteId::fromRemoteId($resource->getRemoteId()),
                $options,
                $token,
            )
            ->willReturn($tag);

        self::assertSame(
            $tag,
            $this->cloudinaryProvider->generateHtmlTag($resource),
        );
    }

    public function testGeneratePictureTagVariation(): void
    {
        $resource = new RemoteResource(
            remoteId: 'upload|image|media/images/image.jpg',
            type: 'image',
            url: 'https://cloudinary.com/test/upload/images/image.jpg',
            md5: 'e522f43cf89aa0afd03387c37e2b6e29',
            name: 'image.jpg',
            size: 95,
            altText: 'Alternate text',
            caption: 'Test title',
        );

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

    public function testGenerateVideoTag(): void
    {
        $resource = new RemoteResource(
            remoteId: 'upload|image|media/videos/example.mp4',
            type: 'video',
            url: 'https://cloudinary.com/test/upload/videos/example.mp4',
            md5: 'e522f43cf89aa0afd03387c37e2b6e29',
            name: 'example.mp4',
            size: 495,
        );

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

    public function testGenerateVideoTagAuthenticated(): void
    {
        $url = 'https://cloudinary.com/test/upload/videos/example.mp4?token=33e5f3527d0ffff8bf0508f912b76a6d';
        $token = AuthToken::fromDuration(60);

        $resource = new AuthenticatedRemoteResource(
            remoteResource: new RemoteResource(
                remoteId: 'upload|image|media/videos/example.mp4',
                type: 'video',
                url: 'https://cloudinary.com/test/upload/videos/example.mp4',
                md5: 'e522f43cf89aa0afd03387c37e2b6e29',
                name: 'example.mp4',
                size: 495,
            ),
            url: $url,
            token: $token,
        );

        $options = [
            'secure' => true,
            'fallback_content' => 'Your browser does not support HTML5 video tags',
            'controls' => true,
            'poster' => [
                'secure' => true,
            ],
            'attributes' => [],
        ];

        $tag = sprintf('<video><source src="%s"></video>', $url);

        $this->gateway
            ->expects(self::once())
            ->method('getVideoTag')
            ->with(
                CloudinaryRemoteId::fromRemoteId($resource->getRemoteId()),
                $options,
                $token,
            )
            ->willReturn($tag);

        self::assertSame(
            $tag,
            $this->cloudinaryProvider->generateHtmlTag($resource),
        );
    }

    public function testGenerateVideoTagAudio(): void
    {
        $resource = new RemoteResource(
            remoteId: 'upload|image|media/songs/example.mp3',
            type: 'audio',
            url: 'https://cloudinary.com/test/upload/songs/example.mp3',
            md5: 'e522f43cf89aa0afd03387c37e2b6e29',
            name: 'example.mp3',
            size: 105,
        );

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

    public function testGenerateVideoTagVariation(): void
    {
        $resource = new RemoteResource(
            remoteId: 'upload|image|media/videos/example.mp4',
            type: 'video',
            url: 'https://cloudinary.com/test/upload/videos/example.mp4',
            md5: 'e522f43cf89aa0afd03387c37e2b6e29',
            name: 'example.mp4',
            size: 495,
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

    public function testGenerateVideoTagThumbnailVariation(): void
    {
        $resource = new RemoteResource(
            remoteId: 'upload|image|media/videos/example.mp4',
            type: 'video',
            url: 'https://cloudinary.com/test/upload/videos/example.mp4',
            md5: 'e522f43cf89aa0afd03387c37e2b6e29',
            name: 'example.mp4',
            size: 495,
            altText: 'Alternate text',
            caption: 'Test caption',
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

    public function testGenerateVideoTagThumbnailVariationAuthenticated(): void
    {
        $url = 'https://cloudinary.com/test/upload/videos/example.mp4?token=9ce0b407ee070a6865b97a6fc6a5d854';
        $token = AuthToken::fromDuration(60);

        $resource = new AuthenticatedRemoteResource(
            remoteResource: new RemoteResource(
                remoteId: 'upload|image|media/videos/example.mp4',
                type: 'video',
                url: 'https://cloudinary.com/test/upload/videos/example.mp4',
                md5: 'e522f43cf89aa0afd03387c37e2b6e29',
                name: 'example.mp4',
                size: 495,
                altText: 'Alternate text',
                caption: 'Test caption',
            ),
            url: $url,
            token: $token,
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
            'start_offset' => 'auto',
        ];

        $thumbnailUrl = 'https://cloudinary.com/test/c_5_10_200_100/upload/videos/example_thumb.jpg?token=9ce0b407ee070a6865b97a6fc6a5d854';

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

        $tag = '<picture><img alt="Alternate text" title="Test caption" src="https://cloudinary.com/test/c_5_10_200_100/upload/videos/example?token=9ce0b407ee070a6865b97a6fc6a5d854"></picture>';

        $this->gateway
            ->expects(self::once())
            ->method('getImageTag')
            ->with(
                CloudinaryRemoteId::fromRemoteId($resource->getRemoteId()),
                $options,
                $token,
            )
            ->willReturn($tag);

        $tag = '<picture><img alt="Alternate text" title="Test caption" src="https://cloudinary.com/test/c_5_10_200_100/upload/videos/example_thumb.jpg?token=9ce0b407ee070a6865b97a6fc6a5d854"></picture>';

        self::assertSame(
            $tag,
            $this->cloudinaryProvider->generateRawVariationHtmlTag($resource, $transformations, [], false, true),
        );
    }

    public function testGenerateVideoTagThumbnailVariationInvalid(): void
    {
        $resource = new RemoteResource(
            remoteId: 'upload|image|media/videos/example.mp4',
            type: 'video',
            url: 'https://cloudinary.com/test/upload/videos/example.mp4',
            md5: 'e522f43cf89aa0afd03387c37e2b6e29',
            name: 'example.mp4',
            size: 495,
            altText: 'Alternate text',
            caption: 'Test caption',
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

        $tag = '<picture><img alt="Alternate text" title="Test caption" src2="https://cloudinary.com/test/c_5_10_200_100/upload/videos/example"></picture>';

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
            $this->cloudinaryProvider->generateRawVariationHtmlTag($resource, $transformations, [], false, true),
        );
    }

    public function testGenerateAudioTag(): void
    {
        $resource = new RemoteResource(
            remoteId: 'upload|image|media/songs/example.mp3',
            type: 'audio',
            url: 'https://cloudinary.com/test/upload/songs/example.mp3',
            md5: 'e522f43cf89aa0afd03387c37e2b6e29',
            name: 'example.mp3',
            size: 105,
        );

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

    public function testGenerateAudioTagAuthenticated(): void
    {
        $url = 'https://cloudinary.com/test/upload/songs/example.mp3?c9533334c167b12a99f2ebc71e4de34f';
        $token = AuthToken::fromDuration(60);

        $resource = new AuthenticatedRemoteResource(
            remoteResource: new RemoteResource(
                remoteId: 'upload|image|media/songs/example.mp3',
                type: 'audio',
                url: 'https://cloudinary.com/test/upload/songs/example.mp3',
                md5: 'e522f43cf89aa0afd03387c37e2b6e29',
                name: 'example.mp3',
                size: 105,
            ),
            url: $url,
            token: $token,
        );

        $htmlAttributes = [
            'width' => '100%',
        ];

        $options = [
            'secure' => true,
            'fallback_content' => 'Your browser does not support HTML5 audio tags',
            'controls' => true,
            'attributes' => $htmlAttributes,
        ];

        $tag = sprintf('<audio width="100%%"><source src="%s"></audio>', $url);

        $this->gateway
            ->expects(self::once())
            ->method('getVideoTag')
            ->with(
                CloudinaryRemoteId::fromRemoteId($resource->getRemoteId()),
                $options,
                $token,
            )
            ->willReturn($tag);

        self::assertSame(
            $tag,
            $this->cloudinaryProvider->generateRawVariationHtmlTag($resource, [], $htmlAttributes),
        );
    }

    public function testGenerateDocumentTag(): void
    {
        $resource = new RemoteResource(
            remoteId: 'upload|image|media/doc/example.pdf',
            type: 'document',
            url: 'https://cloudinary.com/test/upload/doc/example.pdf',
            md5: 'e522f43cf89aa0afd03387c37e2b6e29',
            name: 'example.pdf',
            size: 35,
        );

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
