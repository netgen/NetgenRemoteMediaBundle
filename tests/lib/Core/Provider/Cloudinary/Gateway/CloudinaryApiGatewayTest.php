<?php

declare(strict_types=1);

namespace Netgen\RemoteMedia\Tests\Core\Provider\Cloudinary\Gateway;

use ArrayObject;
use Cloudinary;
use Cloudinary\Api;
use Cloudinary\Api\Response as CloudinaryApiResponse;
use Cloudinary\Search;
use Cloudinary\Uploader as CloudinaryUploader;
use DateTimeImmutable;
use Netgen\RemoteMedia\API\Factory\RemoteResource as RemoteResourceFactoryInterface;
use Netgen\RemoteMedia\API\Factory\SearchResult as SearchResultFactoryInterface;
use Netgen\RemoteMedia\API\Search\Query;
use Netgen\RemoteMedia\API\Search\Result;
use Netgen\RemoteMedia\API\Values\AuthToken;
use Netgen\RemoteMedia\API\Values\RemoteResource;
use Netgen\RemoteMedia\API\Values\StatusData;
use Netgen\RemoteMedia\Core\Provider\Cloudinary\CloudinaryRemoteId;
use Netgen\RemoteMedia\Core\Provider\Cloudinary\Converter\ResourceType as ResourceTypeConverter;
use Netgen\RemoteMedia\Core\Provider\Cloudinary\Converter\VisibilityType as VisibilityTypeConverter;
use Netgen\RemoteMedia\Core\Provider\Cloudinary\Factory\CloudinaryInstance;
use Netgen\RemoteMedia\Core\Provider\Cloudinary\Gateway\CloudinaryApiGateway;
use Netgen\RemoteMedia\Core\Provider\Cloudinary\Resolver\AuthToken as AuthTokenResolver;
use Netgen\RemoteMedia\Core\Provider\Cloudinary\Resolver\SearchExpression as SearchExpressionResolver;
use Netgen\RemoteMedia\Exception\FolderNotFoundException;
use Netgen\RemoteMedia\Exception\RemoteResourceNotFoundException;
use Netgen\RemoteMedia\Tests\AbstractTestCase;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\MockObject\MockObject;
use stdClass;

use function json_encode;

#[CoversClass(CloudinaryApiGateway::class)]
class CloudinaryApiGatewayTest extends AbstractTestCase
{
    protected const CLOUD_NAME = 'testcloud';

    protected const API_KEY = 'apikey';

    protected const API_SECRET = 'secret';

    protected const UPLOAD_PREFIX = 'https://api.cloudinary.com';

    protected CloudinaryApiGateway $apiGateway;

    protected MockObject|Cloudinary $cloudinaryMock;

    protected MockObject|Api $cloudinaryApiMock;

    protected MockObject|Search $cloudinarySearchMock;

    protected MockObject|RemoteResourceFactoryInterface $remoteResourceFactoryMock;

    protected MockObject|SearchResultFactoryInterface $searchResultFactoryMock;

    protected function setUp(): void
    {
        $this->cloudinaryMock = $this->createMock(Cloudinary::class);
        $this->cloudinaryApiMock = $this->createMock(Api::class);
        $this->cloudinarySearchMock = $this->createMock(Search::class);
        $this->remoteResourceFactoryMock = $this->createMock(RemoteResourceFactoryInterface::class);
        $this->searchResultFactoryMock = $this->createMock(SearchResultFactoryInterface::class);

        $cloudinaryInstanceFactory = new CloudinaryInstance(
            self::CLOUD_NAME,
            self::API_KEY,
            self::API_SECRET,
            self::UPLOAD_PREFIX,
        );

        $this->apiGateway = new CloudinaryApiGateway(
            $cloudinaryInstanceFactory->create(),
            $this->remoteResourceFactoryMock,
            $this->searchResultFactoryMock,
            new SearchExpressionResolver(
                new ResourceTypeConverter(),
                new VisibilityTypeConverter(),
            ),
            new AuthTokenResolver('38128319a3a49e1d589a31a217e1a3f8'),
        );

        $this->apiGateway->setServices(
            $this->cloudinaryMock,
            new CloudinaryUploader(),
            $this->cloudinaryApiMock,
            $this->cloudinarySearchMock,
        );
    }

    public function testUsage(): void
    {
        $data = [
            'plan' => 'Advanced',
            'last_updated' => '2020-10-06',
            'transformations' => [
                'usage' => 234,
                'credits_usage' => 2.54,
            ],
            'objects' => [
                'usage' => 3243,
            ],
            'bandwidth' => [
                'usage' => 456575,
                'credits_usage' => 123.12,
            ],
            'storage' => [
                'usage' => 23435454,
                'credits_usage' => 98.22,
            ],
            'credits' => [
                'usage' => 68.80,
                'limit' => 135.0,
                'used_percent' => 50.96,
            ],
            'requests' => 3243545,
            'resources' => 3543,
            'derived_resources' => 34435,
            'media_limits' => [
                'image_max_size_bytes' => 234343,
                'video_max_size_bytes' => 3254545,
                'raw_max_size_bytes' => 34543432,
                'image_max_px' => 500000,
                'asset_max_total_px' => 2000000,
            ],
        ];

        $response = new stdClass();
        $response->body = json_encode($data);
        $response->headers = [
            'X-FeatureRateLimit-Reset' => '12.11.2021 17:00:00',
            'X-FeatureRateLimit-Limit' => 1567654320,
            'X-FeatureRateLimit-Remaining' => 1965,
        ];

        $response = new CloudinaryApiResponse($response);

        $this->cloudinaryApiMock
            ->expects(self::once())
            ->method('usage')
            ->willReturn($response);

        $usage = $this->apiGateway->usage();

        self::assertInstanceOf(
            StatusData::class,
            $usage,
        );

        self::assertCount(
            16,
            $usage->all(),
        );

        self::assertTrue($usage->has('plan'));
        self::assertSame(
            'Advanced',
            $usage->get('plan'),
        );

        self::assertTrue($usage->has('rate_limit_allowed'));
        self::assertSame(
            1567654320,
            $usage->get('rate_limit_allowed'),
        );

        self::assertTrue($usage->has('rate_limit_remaining'));
        self::assertSame(
            1965,
            $usage->get('rate_limit_remaining'),
        );

        self::assertTrue($usage->has('rate_limit_reset_at'));
        self::assertSame(
            '12.11.2021 17:00:00',
            $usage->get('rate_limit_reset_at'),
        );

        self::assertTrue($usage->has('objects'));
        self::assertSame(
            3243,
            $usage->get('objects'),
        );

        self::assertTrue($usage->has('resources'));
        self::assertSame(
            3543,
            $usage->get('resources'),
        );

        self::assertTrue($usage->has('derived_resources'));
        self::assertSame(
            34435,
            $usage->get('derived_resources'),
        );

        self::assertTrue($usage->has('transformations_usage'));
        self::assertSame(
            234,
            $usage->get('transformations_usage'),
        );

        self::assertTrue($usage->has('transformations_credit_usage'));
        self::assertSame(
            2.54,
            $usage->get('transformations_credit_usage'),
        );

        self::assertTrue($usage->has('storage_usage'));
        self::assertSame(
            '22.35 MB',
            $usage->get('storage_usage'),
        );

        self::assertTrue($usage->has('storage_credit_usage'));
        self::assertSame(
            98.22,
            $usage->get('storage_credit_usage'),
        );

        self::assertTrue($usage->has('bandwidth_usage'));
        self::assertSame(
            '445.87 KB',
            $usage->get('bandwidth_usage'),
        );

        self::assertTrue($usage->has('bandwidth_credit_usage'));
        self::assertSame(
            123.12,
            $usage->get('bandwidth_credit_usage'),
        );

        self::assertTrue($usage->has('credits_usage'));
        self::assertSame(
            68.80,
            $usage->get('credits_usage'),
        );

        self::assertTrue($usage->has('credits_limit'));
        self::assertSame(
            135,
            $usage->get('credits_limit'),
        );

        self::assertFalse($usage->has('random_key'));
        self::assertNull($usage->get('random_key'));
    }

    public function testIsEncryptionEnabled(): void
    {
        self::assertTrue($this->apiGateway->isEncryptionEnabled());

        $cloudinaryInstanceFactory = new CloudinaryInstance(
            self::CLOUD_NAME,
            self::API_KEY,
            self::API_SECRET,
            self::UPLOAD_PREFIX,
        );

        $apiGateway = new CloudinaryApiGateway(
            $cloudinaryInstanceFactory->create(),
            $this->remoteResourceFactoryMock,
            $this->searchResultFactoryMock,
            new SearchExpressionResolver(
                new ResourceTypeConverter(),
                new VisibilityTypeConverter(),
            ),
            new AuthTokenResolver(),
        );

        self::assertFalse($apiGateway->isEncryptionEnabled());
    }

    public function testCountResources(): void
    {
        $this->cloudinaryApiMock
            ->expects(self::once())
            ->method('usage')
            ->willReturn(['resources' => 1200]);

        self::assertSame(
            1200,
            $this->apiGateway->countResources(),
        );
    }

    public function testCountResourcesInFolder(): void
    {
        $expression = 'folder:folderName/*';

        $this->cloudinarySearchMock
            ->expects(self::once())
            ->method('expression')
            ->with($expression)
            ->willReturn($this->cloudinarySearchMock);

        $this->cloudinarySearchMock
            ->expects(self::once())
            ->method('max_results')
            ->with(0)
            ->willReturn($this->cloudinarySearchMock);

        $this->cloudinarySearchMock
            ->expects(self::once())
            ->method('execute')
            ->willReturn($this->getSearchResponse());

        self::assertSame(
            200,
            $this->apiGateway->countResourcesInFolder('folderName'),
        );
    }

    public function testListFolders(): void
    {
        $folders = [
            [
                'name' => 'folder_1',
                'path' => 'folder_1',
            ],
            [
                'name' => 'folder_2',
                'path' => 'folder_2',
            ],
        ];

        $this->cloudinaryApiMock
            ->expects(self::once())
            ->method('root_folders')
            ->willReturn(
                new ArrayObject(
                    ['folders' => $folders],
                ),
            );

        self::assertSame(
            [
                'folder_1',
                'folder_2',
            ],
            $this->apiGateway->listFolders(),
        );
    }

    public function testListSubFolders(): void
    {
        $folders = [
            [
                'name' => 'folder_1/subfolder_1',
                'path' => 'folder_1/subfolder_1',
            ],
            [
                'name' => 'folder_1/subfolder_2',
                'path' => 'folder_1/subfolder_2',
            ],
        ];

        $this->cloudinaryApiMock
            ->expects(self::once())
            ->method('subfolders')
            ->with('folder_1')
            ->willReturn(
                new ArrayObject(
                    ['folders' => $folders],
                ),
            );

        self::assertSame(
            [
                'folder_1/subfolder_1',
                'folder_1/subfolder_2',
            ],
            $this->apiGateway->listSubFolders('folder_1'),
        );
    }

    public function testListSubFoldersInNonExistingParent(): void
    {
        $this->cloudinaryApiMock
            ->expects(self::once())
            ->method('subfolders')
            ->with('non_existing_folder/non_existing_subfolder')
            ->willThrowException(new Api\NotFound());

        self::expectException(FolderNotFoundException::class);
        self::expectExceptionMessage('Folder with path "non_existing_folder/non_existing_subfolder" was not found on remote.');

        $this->apiGateway->listSubFolders('non_existing_folder/non_existing_subfolder');
    }

    public function testCreateFolder(): void
    {
        $path = 'folder/subfolder/my_new_folder';

        $this->cloudinaryApiMock
            ->expects(self::once())
            ->method('create_folder')
            ->with($path);

        $this->apiGateway->createFolder($path);
    }

    public function testGet(): void
    {
        $remoteId = CloudinaryRemoteId::fromRemoteId('upload|image|folder/test_image.jpg');

        $cloudinaryResponse = [
            'public_id' => 'folder/test_image.jpg',
            'format' => 'jpg',
            'resource_type' => 'image',
            'type' => 'upload',
            'secure_url' => 'https://res.cloudinary.com/demo/image/upload/folder/test_image.jpg',
        ];

        $this->cloudinaryApiMock
            ->expects(self::once())
            ->method('resource')
            ->with(
                $remoteId->getResourceId(),
                [
                    'type' => $remoteId->getType(),
                    'resource_type' => $remoteId->getResourceType(),
                    'media_metadata' => true,
                    'image_metadata' => true,
                    'exif' => true,
                ],
            )
            ->willReturn($cloudinaryResponse);

        $remoteResource = new RemoteResource(
            remoteId: $remoteId->getRemoteId(),
            type: RemoteResource::TYPE_IMAGE,
            url: 'https://res.cloudinary.com/demo/image/upload/folder/test_image.jpg',
            md5: 'e522f43cf89aa0afd03387c37e2b6e29',
            name: 'test_image.jpg',
            metadata: ['format' => 'jpg'],
        );

        $this->remoteResourceFactoryMock
            ->expects(self::once())
            ->method('create')
            ->with($cloudinaryResponse)
            ->willReturn($remoteResource);

        self::assertRemoteResourceSame(
            $remoteResource,
            $this->apiGateway->get($remoteId),
        );
    }

    public function testGetNotExisting(): void
    {
        $remoteId = CloudinaryRemoteId::fromRemoteId('upload|image|folder/test_image.jpg');

        $this->cloudinaryApiMock
            ->expects(self::once())
            ->method('resource')
            ->with(
                $remoteId->getResourceId(),
                [
                    'type' => $remoteId->getType(),
                    'resource_type' => $remoteId->getResourceType(),
                    'media_metadata' => true,
                    'image_metadata' => true,
                    'exif' => true,
                ],
            )
            ->willThrowException(new Api\NotFound());

        self::expectException(RemoteResourceNotFoundException::class);
        self::expectExceptionMessage('Remote resource with ID "upload|image|folder/test_image.jpg" not found.');

        $this->apiGateway->get($remoteId);
    }

    public function testGetAuthenticatedUrl(): void
    {
        $remoteId = CloudinaryRemoteId::fromRemoteId('upload|image|folder/test_image.jpg');
        $token = AuthToken::fromExpiresAt(new DateTimeImmutable('2023/1/1'));

        self::assertSame(
            'https://res.cloudinary.com/testcloud/image/upload/folder/test_image.jpg?__cld_token__=exp=1672531200~hmac=2dd3d9ad80b2cd7b02589a11e5a5ccfd8d2fbf75eef8d54b84e89c2dfd951255',
            $this->apiGateway->getAuthenticatedUrl($remoteId, $token),
        );
    }

    public function testGetVariationUrl(): void
    {
        $remoteId = CloudinaryRemoteId::fromRemoteId('upload|image|folder/test_image.jpg');
        $transformations = [
            'x' => 50,
            'y' => 50,
            'width' => 300,
            'height' => 200,
            'crop' => 'crop',
        ];

        self::assertSame(
            'https://res.cloudinary.com/testcloud/image/upload/c_crop,h_200,w_300,x_50,y_50/folder/test_image.jpg',
            $this->apiGateway->getVariationUrl($remoteId, $transformations),
        );
    }

    public function testSearch(): void
    {
        $expression = '(resource_type:"image" OR resource_type:"video")'
            . ' AND (((!format="pdf") AND (!format="doc") AND (!format="docx") AND (!format="ppt") AND (!format="pptx")'
            . ' AND (!format="txt") AND (!format="aac") AND (!format="aiff") AND (!format="amr") AND (!format="flac")'
            . ' AND (!format="m4a") AND (!format="mp3") AND (!format="ogg") AND (!format="opus") AND (!format="wav")))'
            . ' AND test* AND (folder:"test_folder") AND (tags:"tag1")';
        $limit = 25;

        $this->cloudinarySearchMock
            ->expects(self::once())
            ->method('expression')
            ->with($expression)
            ->willReturn($this->cloudinarySearchMock);

        $this->cloudinarySearchMock
            ->expects(self::once())
            ->method('max_results')
            ->with($limit)
            ->willReturn($this->cloudinarySearchMock);

        $this->cloudinarySearchMock
            ->expects(self::exactly(2))
            ->method('with_field')
            ->willReturnMap(
                [
                    ['context', $this->cloudinarySearchMock],
                    ['tags', $this->cloudinarySearchMock],
                ],
            );

        $apiResponse = $this->getSearchResponse();

        $this->cloudinarySearchMock
            ->expects(self::once())
            ->method('execute')
            ->willReturn($apiResponse);

        $query = new Query(
            query: 'test',
            types: ['image', 'video'],
            folders: ['test_folder'],
            tags: ['tag1'],
        );

        $searchResult = new Result(
            200,
            '123',
            [
                new RemoteResource(
                    remoteId: 'upload|image|test.jpg',
                    type: 'image',
                    url: 'https://cloudinary.com/test/upload/image/test.jpg',
                    md5: 'e522f43cf89aa0afd03387c37e2b6e29',
                    name: 'test.jpg',
                ),
            ],
        );

        $this->searchResultFactoryMock
            ->expects(self::once())
            ->method('create')
            ->with($apiResponse)
            ->willReturn($searchResult);

        self::assertSearchResultSame(
            $searchResult,
            $this->apiGateway->search($query),
        );
    }

    public function testSearchWithNextCursor(): void
    {
        $expression = '(resource_type:"image" OR resource_type:"video")'
            . ' AND (((!format="pdf") AND (!format="doc") AND (!format="docx") AND (!format="ppt") AND (!format="pptx")'
            . ' AND (!format="txt") AND (!format="aac") AND (!format="aiff") AND (!format="amr") AND (!format="flac")'
            . ' AND (!format="m4a") AND (!format="mp3") AND (!format="ogg") AND (!format="opus") AND (!format="wav")))'
            . ' AND test* AND (folder:"test_folder") AND (tags:"tag1")';
        $limit = 25;
        $nextCursor = 'gfr566455fdg';

        $this->cloudinarySearchMock
            ->expects(self::once())
            ->method('expression')
            ->with($expression)
            ->willReturn($this->cloudinarySearchMock);

        $this->cloudinarySearchMock
            ->expects(self::once())
            ->method('max_results')
            ->with($limit)
            ->willReturn($this->cloudinarySearchMock);

        $this->cloudinarySearchMock
            ->expects(self::exactly(2))
            ->method('with_field')
            ->willReturnMap(
                [
                    ['context', $this->cloudinarySearchMock],
                    ['tags', $this->cloudinarySearchMock],
                ],
            );

        $apiResponse = $this->getSearchResponse();

        $this->cloudinarySearchMock
            ->expects(self::once())
            ->method('next_cursor')
            ->willReturn($nextCursor)
            ->willReturn($this->cloudinarySearchMock);

        $this->cloudinarySearchMock
            ->expects(self::once())
            ->method('execute')
            ->willReturn($apiResponse);

        $query = new Query(
            query: 'test',
            types: ['image', 'video'],
            folders: ['test_folder'],
            tags: ['tag1'],
            nextCursor: $nextCursor,
        );

        $searchResult = new Result(
            200,
            '123',
            [
                new RemoteResource(
                    remoteId: 'upload|image|test.jpg',
                    type: 'image',
                    url: 'https://cloudinary.com/test/upload/image/test.jpg',
                    md5: 'e522f43cf89aa0afd03387c37e2b6e29',
                    name: 'test.jpg',
                ),
            ],
        );

        $this->searchResultFactoryMock
            ->expects(self::once())
            ->method('create')
            ->with($apiResponse)
            ->willReturn($searchResult);

        self::assertSearchResultSame(
            $searchResult,
            $this->apiGateway->search($query),
        );
    }

    public function testSearchCount(): void
    {
        $expression = '(resource_type:"image" OR resource_type:"video")'
            . ' AND (((!format="pdf") AND (!format="doc") AND (!format="docx") AND (!format="ppt") AND (!format="pptx")'
            . ' AND (!format="txt") AND (!format="aac") AND (!format="aiff") AND (!format="amr") AND (!format="flac")'
            . ' AND (!format="m4a") AND (!format="mp3") AND (!format="ogg") AND (!format="opus") AND (!format="wav")))'
            . ' AND test* AND (folder:"test_folder") AND (tags:"tag1")';

        $this->cloudinarySearchMock
            ->expects(self::once())
            ->method('expression')
            ->with($expression)
            ->willReturn($this->cloudinarySearchMock);

        $this->cloudinarySearchMock
            ->expects(self::once())
            ->method('max_results')
            ->with(0)
            ->willReturn($this->cloudinarySearchMock);

        $apiResponse = $this->getSearchResponse();

        $this->cloudinarySearchMock
            ->expects(self::once())
            ->method('execute')
            ->willReturn($apiResponse);

        $query = new Query(
            query: 'test',
            types: ['image', 'video'],
            folders: ['test_folder'],
            tags: ['tag1'],
        );

        self::assertSame(
            200,
            $this->apiGateway->searchCount($query),
        );
    }

    public function testListTags(): void
    {
        $this->cloudinaryApiMock
            ->expects(self::exactly(3))
            ->method('tags')
            ->willReturnMap(
                [
                    [
                        [
                            'max_results' => 500,
                        ],
                        [
                            'tags' => [
                                'tag1',
                                'tag2',
                                'tag3',
                            ],
                            'next_cursor' => 'gegtrgofu0439ree9i0',
                        ],
                    ],
                    [
                        [
                            'max_results' => 500,
                            'next_cursor' => 'gegtrgofu0439ree9i0',
                        ],
                        [
                            'tags' => [
                                'tag4',
                                'tag5',
                                'tag6',
                            ],
                            'next_cursor' => 'ewtfdrejofjpjoijpo04',
                        ],
                    ],
                    [
                        [
                            'max_results' => 500,
                            'next_cursor' => 'ewtfdrejofjpjoijpo04',
                        ],
                        [
                            'tags' => [
                                'tag7',
                            ],
                        ],
                    ],
                ],
            );

        self::assertSame(
            [
                'tag1',
                'tag2',
                'tag3',
                'tag4',
                'tag5',
                'tag6',
                'tag7',
            ],
            $this->apiGateway->listTags(),
        );
    }

    public function testSearchResourceByHash(): void
    {
        $expression = '(resource_type:"image" OR resource_type:"video")'
            . ' AND (((!format="pdf") AND (!format="doc") AND (!format="docx") AND (!format="ppt") AND (!format="pptx")'
            . ' AND (!format="txt") AND (!format="aac") AND (!format="aiff") AND (!format="amr") AND (!format="flac")'
            . ' AND (!format="m4a") AND (!format="mp3") AND (!format="ogg") AND (!format="opus") AND (!format="wav")))'
            . ' AND test* AND (folder:"test_folder") AND (etag="e522f43cf89aa0afd03387c37e2b6e29")';
        $limit = 1;

        $this->cloudinarySearchMock
            ->expects(self::once())
            ->method('expression')
            ->with($expression)
            ->willReturn($this->cloudinarySearchMock);

        $this->cloudinarySearchMock
            ->expects(self::once())
            ->method('max_results')
            ->with($limit)
            ->willReturn($this->cloudinarySearchMock);

        $this->cloudinarySearchMock
            ->expects(self::exactly(2))
            ->method('with_field')
            ->willReturnMap(
                [
                    ['context', $this->cloudinarySearchMock],
                    ['tags', $this->cloudinarySearchMock],
                ],
            );

        $apiResponse = $this->getSearchResponse();

        $this->cloudinarySearchMock
            ->expects(self::once())
            ->method('execute')
            ->willReturn($apiResponse);

        $query = new Query(
            query: 'test',
            types: ['image', 'video'],
            folders: ['test_folder'],
            md5s: ['e522f43cf89aa0afd03387c37e2b6e29'],
            limit: 1,
        );

        $searchResult = new Result(
            1,
            '123',
            [
                new RemoteResource(
                    remoteId: 'upload|image|test.jpg',
                    type: 'image',
                    url: 'https://cloudinary.com/test/upload/image/test.jpg',
                    md5: 'e522f43cf89aa0afd03387c37e2b6e29',
                    name: 'test.jpg',
                ),
            ],
        );

        $this->searchResultFactoryMock
            ->expects(self::once())
            ->method('create')
            ->with($apiResponse)
            ->willReturn($searchResult);

        self::assertSearchResultSame(
            $searchResult,
            $this->apiGateway->search($query),
        );
    }

    public function testGetVideoThumbnail(): void
    {
        $cloudinaryRemoteId = CloudinaryRemoteId::fromRemoteId('upload|video|media/example');

        self::assertSame(
            'https://res.cloudinary.com/testcloud/video/upload/media/example.jpg',
            $this->apiGateway->getVideoThumbnail($cloudinaryRemoteId),
        );
    }

    public function testGetVideoThumbnailAuthenticated(): void
    {
        $cloudinaryRemoteId = CloudinaryRemoteId::fromRemoteId('upload|video|media/example');
        $token = AuthToken::fromExpiresAt(new DateTimeImmutable('2023/1/1'));

        self::assertSame(
            'https://res.cloudinary.com/testcloud/video/upload/media/example.jpg?__cld_token__=exp=1672531200~hmac=62ddaa466e7acbd07699201e33c8c1865b78b91365bd727f7b88ac524f02095b',
            $this->apiGateway->getVideoThumbnail($cloudinaryRemoteId, [], $token),
        );
    }

    public function testGetImageTag(): void
    {
        $cloudinaryRemoteId = CloudinaryRemoteId::fromRemoteId('upload|image|media/example');

        self::assertSame(
            "<img src='https://res.cloudinary.com/testcloud/image/upload/media/example' />",
            $this->apiGateway->getImageTag($cloudinaryRemoteId),
        );
    }

    public function testGetImageTagAuthenticated(): void
    {
        $cloudinaryRemoteId = CloudinaryRemoteId::fromRemoteId('upload|image|media/example');
        $token = AuthToken::fromExpiresAt(new DateTimeImmutable('2023/1/1'));

        self::assertSame(
            "<img src='https://res.cloudinary.com/testcloud/image/upload/media/example?__cld_token__=exp=1672531200~hmac=7de9d88403fd7cfa56802fa4a6371d32866df3b23ccfa769e45ce4b7e297045a' />",
            $this->apiGateway->getImageTag($cloudinaryRemoteId, [], $token),
        );
    }

    public function testGetVideoTag(): void
    {
        $cloudinaryRemoteId = CloudinaryRemoteId::fromRemoteId('upload|video|media/example');

        self::assertSame(
            "<video poster='https://res.cloudinary.com/testcloud/video/upload/media/example.jpg'>"
            . "<source src='https://res.cloudinary.com/testcloud/video/upload/media/example.webm' type='video/webm'>"
            . "<source src='https://res.cloudinary.com/testcloud/video/upload/media/example.mp4' type='video/mp4'>"
            . "<source src='https://res.cloudinary.com/testcloud/video/upload/media/example.ogv' type='video/ogg'></video>",
            $this->apiGateway->getVideoTag($cloudinaryRemoteId),
        );
    }

    public function testGetVideoTagAuthenticated(): void
    {
        $cloudinaryRemoteId = CloudinaryRemoteId::fromRemoteId('upload|video|media/example');
        $token = AuthToken::fromExpiresAt(new DateTimeImmutable('2023/1/1'));

        self::assertSame(
            "<video poster='https://res.cloudinary.com/testcloud/video/upload/media/example.jpg?__cld_token__=exp=1672531200~hmac=62ddaa466e7acbd07699201e33c8c1865b78b91365bd727f7b88ac524f02095b'>"
            . "<source src='https://res.cloudinary.com/testcloud/video/upload/media/example.webm?__cld_token__=exp=1672531200~hmac=a5cfcc4e4decf5525ffd94122b7f8132be63ca59a9abb5daf6b8325c9e26ba4e' type='video/webm'>"
            . "<source src='https://res.cloudinary.com/testcloud/video/upload/media/example.mp4?__cld_token__=exp=1672531200~hmac=7537ec69571888e23b74d9ab811da1125bc8683f035b449134557311b5835571' type='video/mp4'>"
            . "<source src='https://res.cloudinary.com/testcloud/video/upload/media/example.ogv?__cld_token__=exp=1672531200~hmac=c145ac2c614b225df4fe20525881ee8b2e707febf78c0c927d6e75b0a2decfda' type='video/ogg'></video>",
            $this->apiGateway->getVideoTag($cloudinaryRemoteId, [], $token),
        );
    }

    private function getCloudinaryResponse(array $data): CloudinaryApiResponse
    {
        $response = new stdClass();
        $response->body = json_encode($data);
        $response->responseCode = 200;
        $response->headers = [
            'X-FeatureRateLimit-Reset' => 'test',
            'X-FeatureRateLimit-Limit' => 'test',
            'X-FeatureRateLimit-Remaining' => 'test',
        ];

        return new CloudinaryApiResponse($response);
    }

    private function getSearchResponse(): CloudinaryApiResponse
    {
        return $this->getCloudinaryResponse([
            'total_count' => 200,
            'next_cursor' => '123',
            'resources' => [],
        ]);
    }
}
