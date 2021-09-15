<?php

declare(strict_types=1);

namespace Netgen\RemoteMedia\Tests\Core\Provider\Cloudinary\Gateway;

use ArrayObject;
use Cloudinary;
use Cloudinary\Api;
use Cloudinary\Search;
use Cloudinary\Uploader;
use Netgen\RemoteMedia\API\Search\Query;
use Netgen\RemoteMedia\API\Search\Result;
use Netgen\RemoteMedia\Core\Provider\Cloudinary\Gateway\CloudinaryApiGateway;
use PHPUnit\Framework\Constraint\LogicalOr;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use stdClass;
use function json_encode;

final class CloudinaryApiGatewayTest extends TestCase
{
    protected CloudinaryApiGateway $apiGateway;

    protected MockObject $cloudinary;

    protected MockObject $cloudinaryUploader;

    protected MockObject $cloudinaryApi;

    protected MockObject $cloudinarySearch;

    protected function setUp(): void
    {
        $this->cloudinary = $this->createMock(Cloudinary::class);
        $this->cloudinaryUploader = $this->createMock(Uploader::class);
        $this->cloudinaryApi = $this->createMock(Api::class);
        $this->cloudinarySearch = $this->createMock(Search::class);

        $this->setUpSearch();

        $apiGateway = new CloudinaryApiGateway();
        $apiGateway->setServices($this->cloudinary, $this->cloudinaryUploader, $this->cloudinaryApi, $this->cloudinarySearch);
        $apiGateway->setInternalLimit(1000);
        $this->apiGateway = $apiGateway;
    }

    /**
     * @covers \Netgen\RemoteMedia\Core\Provider\Cloudinary\Gateway\CloudinaryApiGateway::getVariationUrl
     */
    public function testGetVariationUrl()
    {
        $source = 'test.jpg';
        $options = [
            'transformations' => [
                'crop' => [100, 200],
            ],
            'cloud_name' => 'testcloud',
            'secure' => true,
        ];

        self::assertSame(
            'https://res.cloudinary.com/testcloud/image/upload/test.jpg',
            $this->apiGateway->getVariationUrl($source, $options),
        );
    }

    /**
     * @covers \Netgen\RemoteMedia\Core\Provider\Cloudinary\Gateway\CloudinaryApiGateway::buildSearchExpression
     * @covers \Netgen\RemoteMedia\Core\Provider\Cloudinary\Gateway\CloudinaryApiGateway::search
     */
    public function testSearchByTags(): void
    {
        $query = new Query(
            '',
            'image',
            25,
            null,
            'tag',
        );

        $this->cloudinarySearch
            ->expects(self::once())
            ->method('expression');

        $this->cloudinarySearch
            ->expects(self::once())
            ->method('max_results');

        $this->cloudinarySearch
            ->expects(self::exactly(2))
            ->method('with_field');

        $apiResponse = new Api\Response($this->getSearchResponse());

        $this->cloudinarySearch
            ->expects(self::once())
            ->method('execute')
            ->willReturn($apiResponse);

        $expectedResult = Result::fromResponse($apiResponse);
        $result = $this->apiGateway->search($query);

        self::assertSame(
            $expectedResult->getTotalCount(),
            $result->getTotalCount(),
        );

        self::assertSame(
            $expectedResult->getNextCursor(),
            $result->getNextCursor(),
        );

        self::assertSame(
            $expectedResult->getResources(),
            $result->getResources(),
        );
    }

    /**
     * @covers \Netgen\RemoteMedia\Core\Provider\Cloudinary\Gateway\CloudinaryApiGateway::buildSearchExpression
     * @covers \Netgen\RemoteMedia\Core\Provider\Cloudinary\Gateway\CloudinaryApiGateway::search
     */
    public function testSearch(): void
    {
        $query = new Query('query', 'image', 25);

        $this->cloudinarySearch
            ->expects(self::once())
            ->method('expression');

        $this->cloudinarySearch
            ->expects(self::once())
            ->method('max_results');

        $this->cloudinarySearch
            ->expects(self::exactly(2))
            ->method('with_field');

        $apiResponse = new Api\Response($this->getSearchResponse());

        $this->cloudinarySearch
            ->expects(self::once())
            ->method('execute')
            ->willReturn($apiResponse);

        $expectedResult = Result::fromResponse($apiResponse);
        $result = $this->apiGateway->search($query);

        self::assertSame(
            $expectedResult->getTotalCount(),
            $result->getTotalCount(),
        );

        self::assertSame(
            $expectedResult->getNextCursor(),
            $result->getNextCursor(),
        );

        self::assertSame(
            $expectedResult->getResources(),
            $result->getResources(),
        );
    }

    /**
     * @covers \Netgen\RemoteMedia\Core\Provider\Cloudinary\Gateway\CloudinaryApiGateway::buildSearchExpression
     * @covers \Netgen\RemoteMedia\Core\Provider\Cloudinary\Gateway\CloudinaryApiGateway::search
     */
    public function testSearchWithMoreResults(): void
    {
        $query = new Query(
            'query',
            'image',
            25,
            null,
            null,
            '823b',
        );

        $this->cloudinarySearch
            ->expects(self::once())
            ->method('expression');

        $this->cloudinarySearch
            ->expects(self::once())
            ->method('max_results');

        $this->cloudinarySearch
            ->expects(self::exactly(2))
            ->method('with_field');

        $this->cloudinarySearch
            ->expects(self::once())
            ->method('next_cursor');

        $apiResponse = new Api\Response($this->getSearchResponse());

        $this->cloudinarySearch
            ->expects(self::once())
            ->method('execute')
            ->willReturn($apiResponse);

        $expectedResult = Result::fromResponse($apiResponse);
        $result = $this->apiGateway->search($query);

        self::assertSame(
            $expectedResult->getTotalCount(),
            $result->getTotalCount(),
        );

        self::assertSame(
            $expectedResult->getNextCursor(),
            $result->getNextCursor(),
        );

        self::assertSame(
            $expectedResult->getResources(),
            $result->getResources(),
        );
    }

    /**
     * @covers \Netgen\RemoteMedia\Core\Provider\Cloudinary\Gateway\CloudinaryApiGateway::listFolders
     */
    public function testListFolders(): void
    {
        $folders = [
            'folder_1',
            'folder_2',
        ];

        $this->cloudinaryApi
            ->expects(self::once())
            ->method('root_folders')
            ->willReturn(
                new ArrayObject(
                    ['folders' => $folders],
                ),
            );

        self::assertSame(
            $folders,
            $this->apiGateway->listFolders(),
        );
    }

    /**
     * @covers \Netgen\RemoteMedia\Core\Provider\Cloudinary\Gateway\CloudinaryApiGateway::listSubFolders
     */
    public function testListSubFolders(): void
    {
        $folders = [
            'folder_1/subfolder_1',
            'folder_1/subfolder_2',
        ];

        $this->cloudinaryApi
            ->expects(self::once())
            ->method('subfolders')
            ->with('folder_1')
            ->willReturn(
                new ArrayObject(
                    ['folders' => $folders],
                ),
            );

        self::assertSame(
            $folders,
            $this->apiGateway->listSubFolders('folder_1'),
        );
    }

    /**
     * @covers \Netgen\RemoteMedia\Core\Provider\Cloudinary\Gateway\CloudinaryApiGateway::countResources
     */
    public function testCountResources(): void
    {
        $this->cloudinaryApi
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

        $this->cloudinarySearch
            ->expects(self::once())
            ->method('expression')
            ->with($expression);

        $this->cloudinarySearch
            ->expects(self::once())
            ->method('max_results')
            ->with(0);

        $this->cloudinarySearch
            ->expects(self::once())
            ->method('execute')
            ->willReturn(
                new Api\Response($this->getSearchResponse()),
            );

        self::assertSame(
            200,
            $this->apiGateway->countResourcesInFolder('folderName'),
        );
    }

    /**
     * @covers \Netgen\RemoteMedia\Core\Provider\Cloudinary\Gateway\CloudinaryApiGateway::get
     */
    public function testGetNotExistingResource(): void
    {
        $options = [
            'resource_type' => 'image',
        ];

        $this->cloudinaryApi
            ->expects(self::once())
            ->method('resource')
            ->with('test_id', $options);

        $result = $this->apiGateway->get('test_id', 'image');

        self::assertSame(
            [],
            $result,
        );
    }

    /**
     * @covers \Netgen\RemoteMedia\Core\Provider\Cloudinary\Gateway\CloudinaryApiGateway::update
     */
    public function testUpdateResource(): void
    {
        $options = [
            'resource_type' => 'image',
        ];

        $this->cloudinaryApi
            ->expects(self::once())
            ->method('update')
            ->with('test_id', $options);

        $this->apiGateway->update('test_id', 'image', $options);
    }

    private function setUpSearch(): void
    {
        $constraints = new LogicalOr();
        $constraints->setConstraints([
            'expression', 'max_results', 'with_field',
        ]);

        $this->cloudinarySearch->expects(self::any())->method($constraints)->willReturnSelf();
    }

    private function getSearchResponse(): stdClass
    {
        $response = new stdClass();
        $response->body = json_encode([
            'total_count' => 200,
            'next_cursor' => '123',
            'resources' => [],
        ]);
        $response->responseCode = 200;
        $response->headers = [
            'X-FeatureRateLimit-Reset' => 'test',
            'X-FeatureRateLimit-Limit' => 'test',
            'X-FeatureRateLimit-Remaining' => 'test',
        ];

        return $response;
    }
}
