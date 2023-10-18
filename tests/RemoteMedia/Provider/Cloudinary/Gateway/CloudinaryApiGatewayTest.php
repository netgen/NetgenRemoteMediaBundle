<?php

declare(strict_types=1);

namespace Netgen\Bundle\RemoteMediaBundle\Tests\RemoteMedia\Provider\Cloudinary\Gateway;

use Cloudinary;
use Cloudinary\Api;
use Cloudinary\Search;
use Cloudinary\Uploader;
use Netgen\Bundle\RemoteMediaBundle\RemoteMedia\Provider\Cloudinary\Gateway\CloudinaryApiGateway;
use Netgen\Bundle\RemoteMediaBundle\RemoteMedia\Provider\Cloudinary\Search\Query;
use PHPUnit\Framework\Constraint\LogicalOr;
use PHPUnit\Framework\TestCase;
use function json_encode;

class CloudinaryApiGatewayTest extends TestCase
{
    /**
     * @var \Netgen\Bundle\RemoteMediaBundle\RemoteMedia\Provider\Cloudinary\Gateway\CloudinaryApiGateway
     */
    protected $apiGateway;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $cloudinary;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $cloudinaryUploader;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $cloudinaryApi;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $cloudinarySearch;

    protected function setUp()
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

        self::assertEquals(
            'https://res.cloudinary.com/testcloud/image/upload/test.jpg',
            $this->apiGateway->getVariationUrl($source, $options)
        );
    }

    public function testSearchByTags()
    {
        $query = new Query('', 'image', 25, null, 'tag');

        $this->cloudinarySearch
            ->expects(self::once())
            ->method('expression');
        $this->cloudinarySearch
            ->expects(self::once())
            ->method('max_results');
        $this->cloudinarySearch
            ->expects(self::exactly(3))
            ->method('with_field');
        $this->cloudinarySearch
            ->expects(self::once())
            ->method('execute')
            ->willReturn(
                new Api\Response($this->getSearchResponse())
            ); // @todo: finish this

        $this->apiGateway->search($query);
    }

    public function testSearch()
    {
        $query = new Query('query', 'image', 25);

        $this->cloudinarySearch
            ->expects(self::once())
            ->method('expression');
        $this->cloudinarySearch
            ->expects(self::once())
            ->method('max_results');
        $this->cloudinarySearch
            ->expects(self::exactly(3))
            ->method('with_field');
        $this->cloudinarySearch
            ->expects(self::once())
            ->method('execute')
            ->willReturn(
                new Api\Response($this->getSearchResponse())
            ); // @todo: finish this

        $this->apiGateway->search($query);
    }

    public function testSearchWithMoreResults()
    {
        $query = new Query('query', 'image', 25, null, null, '823b');

        $this->cloudinarySearch
            ->expects(self::once())
            ->method('expression');
        $this->cloudinarySearch
            ->expects(self::once())
            ->method('max_results');
        $this->cloudinarySearch
            ->expects(self::exactly(3))
            ->method('with_field');
        $this->cloudinarySearch
            ->expects(self::once())
            ->method('next_cursor');
        $this->cloudinarySearch
            ->expects(self::once())
            ->method('execute')
            ->willReturn(
                new Api\Response($this->getSearchResponse())
            ); // @todo: finish this

        $this->apiGateway->search($query);
    }

    public function testListFolders()
    {
        $this->cloudinaryApi->method('root_folders')->willReturn(new \ArrayObject(['folders' => []]));
        $this->cloudinaryApi->expects(self::once())->method('root_folders');
        $this->apiGateway->listFolders();
    }

    public function testCountResources()
    {
        $this->cloudinaryApi->method('usage')->willReturn(new \ArrayObject(['resources' => []]));
        $this->cloudinaryApi->expects(self::once())->method('usage');
        $this->apiGateway->countResources();
    }

    public function testCountResourcesInFolder()
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
                new Api\Response($this->getSearchResponse())
            );

        self::assertEquals(
            200,
            $this->apiGateway->countResourcesInFolder('folderName')
        );
    }

    public function testGetNotExistingResource()
    {
        $options = [
            'resource_type' => 'image',
        ];

        $this->cloudinaryApi
            ->expects(self::once())
            ->method('resource')
            ->with('test_id', $options);

        $result = $this->apiGateway->get('test_id', 'image');

        self::assertEquals(
            [],
            $result
        );
    }

    public function testUpdateResource()
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

    private function setUpSearch()
    {
        $constraints = new LogicalOr();
        $constraints->setConstraints([
            'expression', 'max_results', 'with_field',
        ]);
        $this->cloudinarySearch->expects(self::any())->method($constraints)->willReturnSelf();
    }

    private function getSearchResponse()
    {
        $response = new \stdClass();
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
