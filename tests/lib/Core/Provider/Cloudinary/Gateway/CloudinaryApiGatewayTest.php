<?php

declare(strict_types=1);

namespace Netgen\RemoteMedia\Tests\Core\Provider\Cloudinary\Gateway;

use ArrayObject;
use Cloudinary;
use Cloudinary\Api;
use Cloudinary\Api\Response as CloudinaryApiResponse;
use Cloudinary\Search;
use Cloudinary\Uploader;
use Netgen\RemoteMedia\API\Factory\RemoteResource as RemoteResourceFactoryInterface;
use Netgen\RemoteMedia\API\Factory\SearchResult as SearchResultFactoryInterface;
use Netgen\RemoteMedia\API\Search\Query;
use Netgen\RemoteMedia\API\Search\Result;
use Netgen\RemoteMedia\API\Values\RemoteResource;
use Netgen\RemoteMedia\API\Values\StatusData;
use Netgen\RemoteMedia\Core\Provider\Cloudinary\CloudinaryRemoteId;
use Netgen\RemoteMedia\Core\Provider\Cloudinary\Converter\ResourceType as ResourceTypeConverter;
use Netgen\RemoteMedia\Core\Provider\Cloudinary\Gateway\CloudinaryApiGateway;
use Netgen\RemoteMedia\Core\Provider\Cloudinary\Resolver\SearchExpression as SearchExpressionResolver;
use Netgen\RemoteMedia\Exception\RemoteResourceNotFoundException;
use Netgen\RemoteMedia\Tests\AbstractTest;
use PHPUnit\Framework\MockObject\MockObject;
use stdClass;

use function json_encode;

class CloudinaryApiGatewayTest extends AbstractTest
{
    protected const CLOUD_NAME = 'testcloud';

    protected const API_KEY = 'apikey';

    protected const API_SECRET = 'secret';

    protected CloudinaryApiGateway $apiGateway;

    /**
     * @var \PHPUnit\Framework\MockObject\MockObject|\Cloudinary\Api
     */
    protected MockObject $cloudinaryMock;

    /**
     * @var \PHPUnit\Framework\MockObject\MockObject|\Cloudinary\Uploader
     */
    protected MockObject $cloudinaryUploaderMock;

    /**
     * @var \PHPUnit\Framework\MockObject\MockObject|\Cloudinary\Api
     */
    protected MockObject $cloudinaryApiMock;

    /**
     * @var \PHPUnit\Framework\MockObject\MockObject|\Cloudinary\Search
     */
    protected MockObject $cloudinarySearchMock;

    /**
     * @var \PHPUnit\Framework\MockObject\MockObject|\Netgen\RemoteMedia\API\Factory\RemoteResource
     */
    protected MockObject $remoteResourceFactoryMock;

    /**
     * @var \PHPUnit\Framework\MockObject\MockObject|\Netgen\RemoteMedia\API\Factory\SearchResult
     */
    protected MockObject $searchResultFactoryMock;

    protected function setUp(): void
    {
        $this->cloudinaryMock = $this->createMock(Cloudinary::class);
        $this->cloudinaryUploaderMock = $this->createMock(Uploader::class);
        $this->cloudinaryApiMock = $this->createMock(Api::class);
        $this->cloudinarySearchMock = $this->createMock(Search::class);
        $this->remoteResourceFactoryMock = $this->createMock(RemoteResourceFactoryInterface::class);
        $this->searchResultFactoryMock = $this->createMock(SearchResultFactoryInterface::class);

        $this->apiGateway = new CloudinaryApiGateway(
            $this->remoteResourceFactoryMock,
            $this->searchResultFactoryMock,
            new SearchExpressionResolver(
                new ResourceTypeConverter(),
            ),
        );

        $this->apiGateway->initCloudinary(
            self::CLOUD_NAME,
            self::API_KEY,
            self::API_SECRET,
        );

        $this->apiGateway->setServices(
            $this->cloudinaryMock,
            $this->cloudinaryUploaderMock,
            $this->cloudinaryApiMock,
            $this->cloudinarySearchMock,
        );
    }

    /**
     * @covers \Netgen\RemoteMedia\Core\Provider\Cloudinary\Gateway\CloudinaryApiGateway::__construct
     * @covers \Netgen\RemoteMedia\Core\Provider\Cloudinary\Gateway\CloudinaryApiGateway::formatBytes
     * @covers \Netgen\RemoteMedia\Core\Provider\Cloudinary\Gateway\CloudinaryApiGateway::initCloudinary
     * @covers \Netgen\RemoteMedia\Core\Provider\Cloudinary\Gateway\CloudinaryApiGateway::setServices
     * @covers \Netgen\RemoteMedia\Core\Provider\Cloudinary\Gateway\CloudinaryApiGateway::usage
     */
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

    /**
     * @covers \Netgen\RemoteMedia\Core\Provider\Cloudinary\Gateway\CloudinaryApiGateway::countResources
     */
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

    /**
     * @covers \Netgen\RemoteMedia\Core\Provider\Cloudinary\Gateway\CloudinaryApiGateway::countResourcesInFolder
     */
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

    /**
     * @covers \Netgen\RemoteMedia\Core\Provider\Cloudinary\Gateway\CloudinaryApiGateway::listFolders
     */
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

    /**
     * @covers \Netgen\RemoteMedia\Core\Provider\Cloudinary\Gateway\CloudinaryApiGateway::listSubFolders
     */
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

    /**
     * @covers \Netgen\RemoteMedia\Core\Provider\Cloudinary\Gateway\CloudinaryApiGateway::createFolder
     */
    public function testCreateFolder(): void
    {
        $path = 'folder/subfolder/my_new_folder';

        $this->cloudinaryApiMock
            ->expects(self::once())
            ->method('create_folder')
            ->with($path);

        $this->apiGateway->createFolder($path);
    }

    /**
     * @covers \Netgen\RemoteMedia\Core\Provider\Cloudinary\Gateway\CloudinaryApiGateway::get
     */
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
                ],
            )
            ->willReturn($cloudinaryResponse);

        $remoteResource = new RemoteResource([
            'remoteId' => $remoteId->getRemoteId(),
            'type' => RemoteResource::TYPE_IMAGE,
            'url' => 'https://res.cloudinary.com/demo/image/upload/folder/test_image.jpg',
            'name' => 'test_image.jpg',
            'metadata' => [
                'format' => 'jpg',
            ],
        ]);

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

    /**
     * @covers \Netgen\RemoteMedia\Core\Provider\Cloudinary\Gateway\CloudinaryApiGateway::get
     */
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
                ],
            )
            ->willThrowException(new Api\NotFound());

        self::expectException(RemoteResourceNotFoundException::class);
        self::expectExceptionMessage('Remote resource with ID "upload|image|folder/test_image.jpg" not found.');

        $this->apiGateway->get($remoteId);
    }

    /**
     * @covers \Netgen\RemoteMedia\Core\Provider\Cloudinary\Gateway\CloudinaryApiGateway::update
     */
    public function testUpdate(): void
    {
        $cloudinaryId = CloudinaryRemoteId::fromRemoteId('upload|image|test.jpg');

        $options = [
            'tags' => ['new_tag'],
        ];

        $expectedOptions = [
            'tags' => ['new_tag'],
            'type' => 'upload',
            'resource_type' => 'image',
        ];

        $this->cloudinaryApiMock
            ->expects(self::once())
            ->method('update')
            ->with('test.jpg', $expectedOptions);

        $this->apiGateway->update($cloudinaryId, $options);
    }

    /**
     * @covers \Netgen\RemoteMedia\Core\Provider\Cloudinary\Gateway\CloudinaryApiGateway::getVariationUrl
     */
    public function testGetVariationUrl()
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
            'https://res.cloudinary.com/testcloud/image/upload/c_crop,h_200,w_300,x_50,y_50/v1/folder/test_image.jpg',
            $this->apiGateway->getVariationUrl($remoteId, $transformations),
        );
    }

    /**
     * @covers \Netgen\RemoteMedia\Core\Provider\Cloudinary\Gateway\CloudinaryApiGateway::search
     */
    public function testSearch(): void
    {
        $expression = '(resource_type:"image" OR resource_type:"video") AND test* AND (folder:"test_folder") AND (tags:"tag1")';
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
            ->withConsecutive(['context'], ['tags'])
            ->willReturn($this->cloudinarySearchMock);

        $apiResponse = $this->getSearchResponse();

        $this->cloudinarySearchMock
            ->expects(self::once())
            ->method('execute')
            ->willReturn($apiResponse);

        $query = new Query([
            'query' => 'test',
            'types' => ['image', 'video'],
            'tags' => ['tag1'],
            'folders' => ['test_folder'],
        ]);

        $searchResult = new Result(
            200,
            '123',
            [
                new RemoteResource([
                    'remoteId' => 'upload|image|test.jpg',
                    'type' => 'image',
                    'url' => 'https://cloudinary.com/test/upload/image/test.jpg',
                    'name' => 'test.jpg',
                ]),
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

    /**
     * @covers \Netgen\RemoteMedia\Core\Provider\Cloudinary\Gateway\CloudinaryApiGateway::search
     */
    public function testSearchWithNextCursor(): void
    {
        $expression = '(resource_type:"image" OR resource_type:"video") AND test* AND (folder:"test_folder") AND (tags:"tag1")';
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
            ->withConsecutive(['context'], ['tags'])
            ->willReturn($this->cloudinarySearchMock);

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

        $query = new Query([
            'query' => 'test',
            'types' => ['image', 'video'],
            'tags' => ['tag1'],
            'folders' => ['test_folder'],
            'nextCursor' => $nextCursor,
        ]);

        $searchResult = new Result(
            200,
            '123',
            [
                new RemoteResource([
                    'remoteId' => 'upload|image|test.jpg',
                    'type' => 'image',
                    'url' => 'https://cloudinary.com/test/upload/image/test.jpg',
                    'name' => 'test.jpg',
                ]),
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

    /**
     * @covers \Netgen\RemoteMedia\Core\Provider\Cloudinary\Gateway\CloudinaryApiGateway::searchCount
     */
    public function testSearchCount(): void
    {
        $expression = '(resource_type:"image" OR resource_type:"video") AND test* AND (folder:"test_folder") AND (tags:"tag1")';

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

        $query = new Query([
            'query' => 'test',
            'types' => ['image', 'video'],
            'tags' => ['tag1'],
            'folders' => ['test_folder'],
        ]);

        self::assertSame(
            200,
            $this->apiGateway->searchCount($query),
        );
    }

    /**
     * @covers \Netgen\RemoteMedia\Core\Provider\Cloudinary\Gateway\CloudinaryApiGateway::listTags
     */
    public function testListTags(): void
    {
        $this->cloudinaryApiMock
            ->expects(self::exactly(3))
            ->method('tags')
            ->withConsecutive(
                [[
                    'max_results' => 500,
                ]],
                [[
                    'max_results' => 500,
                    'next_cursor' => 'gegtrgofu0439ree9i0',
                ]],
                [[
                    'max_results' => 500,
                    'next_cursor' => 'ewtfdrejofjpjoijpo04',
                ]],
            )
            ->willReturnOnConsecutiveCalls(
                [
                    'tags' => [
                        'tag1',
                        'tag2',
                        'tag3',
                    ],
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
                [
                    'tags' => [
                        'tag7',
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
