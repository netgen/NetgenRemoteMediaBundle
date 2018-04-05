<?php

namespace Netgen\Bundle\RemoteMediaBundle\Tests\RemoteMedia\Provider\Cloudinary\Gateway;

use Netgen\Bundle\RemoteMediaBundle\RemoteMedia\Provider\Cloudinary\Gateway\CloudinaryApiGateway;
use PHPUnit\Framework\TestCase;

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

    public function setUp()
    {
        $this->cloudinary = $this->createMock('\Cloudinary');
        $this->cloudinaryUploader = $this->createMock('\Cloudinary\Uploader');
        $this->cloudinaryApi = $this->createMock('\Cloudinary\Api');

        $apiGateway = new CloudinaryApiGateway();
        $apiGateway->setServices($this->cloudinary, $this->cloudinaryUploader, $this->cloudinaryApi);
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

        $this->assertEquals(
            'https://res.cloudinary.com/testcloud/image/upload/test.jpg',
            $this->apiGateway->getVariationUrl($source, $options)
        );
    }

    public function testSearchByTags()
    {
        $options = [
            'SearchByTags' => true,
        ];

        $this->cloudinaryApi
            ->expects($this->once())
            ->method('resources_by_tag')
            ->with(
                'query',
                [
                    'tags' => true,
                    'context' => true,
                    'resource_type' => 'image',
                    'max_results' => 500,
                ]
            );

        $this->apiGateway->search('query', $options);
    }

    public function testSearch()
    {
        $apiOptions = [
            'prefix' => 'query',
            'type' => 'upload',
            'tags' => true,
            'max_results' => 500,
        ];

        $this->cloudinaryApi->method('resources')->willReturn(new \ArrayObject(['resources' => []]));

        $this->cloudinaryApi
            ->expects($this->once())
            ->method('resources')
            ->with($apiOptions);

        $this->apiGateway->search('query');
    }

    public function testSearchWithMoreResults()
    {
        $options1 = [
            'prefix' => 'query',
            'type' => 'upload',
            'tags' => true,
            'max_results' => 500,
        ];
        $options2 = $options1 + ['next_cursor' => 123];

        $this->cloudinaryApi
            ->method('resources')
            ->willReturnOnConsecutiveCalls(
                new \ArrayObject(
                    [
                        'resources' => [],
                        'next_cursor' => 123,
                    ]
                ),
                new \ArrayObject(
                    [
                        'resource' => [],
                    ]
                )
            );

        $this->cloudinaryApi
            ->expects($this->exactly(2))
            ->method('resources')
            ->withConsecutive(
                [$options1],
                [$options2]
            );

        $this->apiGateway->search('query');
    }

    public function testListResource()
    {
        $apiOptions = array(
            'max_results' => 500,
            'tags' => true,
            'context' => true,
            'resource_type' => 'image'
        );

        $this->cloudinaryApi->method('resources')->willReturn(new \ArrayObject(['resources' => []]));

        $this->cloudinaryApi
            ->expects($this->once())
            ->method('resources')
            ->with($apiOptions);

        $this->apiGateway->listResources('image', 20, 0);
    }

    public function testListResourcesWithMoreResults()
    {
        $options1 = array(
            'max_results' => 500,
            'tags' => true,
            'context' => true,
            'resource_type' => 'image'
        );
        $options2 = $options1 + array('next_cursor' => 123);

        $this->cloudinaryApi
            ->method('resources')
            ->willReturnOnConsecutiveCalls(
                new \ArrayObject(['resources' => [], 'next_cursor' => 123]),
                new \ArrayObject(['resources' => []])
            );

        $this->cloudinaryApi
            ->expects($this->exactly(2))
            ->method('resources')
            ->withConsecutive([$options1], [$options2]);

        $this->apiGateway->listResources('image', 0, 20);
    }

    public function testListFolders()
    {
        $this->cloudinaryApi->method('root_folders')->willReturn(new \ArrayObject(['folders' => []]));
        $this->cloudinaryApi->expects($this->once())->method('root_folders');
        $this->apiGateway->listFolders();
    }

    public function testCountResources()
    {
        $this->cloudinaryApi->method('usage')->willReturn(new \ArrayObject(['resources' => []]));
        $this->cloudinaryApi->expects($this->once())->method('usage');
        $this->apiGateway->countResources();
    }

    public function testCountResourcesInFolder()
    {
        $options = ['type' => 'upload', 'max_results' => 500, 'prefix' => 'folderName'];

        $this->cloudinaryApi->method('resources')->willReturn(new \ArrayObject(['resources' => []]));

        $this->cloudinaryApi
            ->expects($this->once())
            ->method('resources')
            ->with($options);

        $this->apiGateway->countResourcesInFolder('folderName');
    }

    public function testCountResourcesInFolderWithMoreResults()
    {
        $options1 = ['type' => 'upload', 'max_results' => 500, 'prefix' => 'folderName'];
        $options2 = $options1 + ['next_cursor' => 123];

        $this->cloudinaryApi
            ->method('resources')
            ->willReturnOnConsecutiveCalls(
                new \ArrayObject(['resources' => [], 'next_cursor' => 123]),
                new \ArrayObject(['resources' => []])
            );

        $this->cloudinaryApi
            ->expects($this->exactly(2))
            ->method('resources')
            ->withConsecutive([$options1], [$options2]);

        $this->apiGateway->countResourcesInFolder('folderName');
    }

    public function testGetNotExistingResource()
    {
        $options = array(
            'resource_type' => 'image',
            'max_results' => 1,
            'tags' => true,
            'context' => true,
        );

        $this->cloudinaryApi
            ->method('resources_by_ids')
            ->willReturn(new \ArrayObject([]));

        $this->cloudinaryApi
            ->expects($this->once())
            ->method('resources_by_ids')
            ->with(['test_id'], $options);

        $result = $this->apiGateway->get('test_id', 'image');

        $this->assertEquals(
            [],
            $result
        );
    }

    public function testUpdateResource()
    {
        $options = [];

        $this->cloudinaryApi
            ->expects($this->once())
            ->method('update')
            ->with('test_id', $options);

        $this->apiGateway->update('test_id', $options);
    }

    public function testDeleteResource()
    {
        $this->cloudinaryApi
            ->expects($this->once())
            ->method('delete_resources')
            ->with(['test_id']);

        $this->apiGateway->delete('test_id');
    }
}
