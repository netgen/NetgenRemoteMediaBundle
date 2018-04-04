<?php

namespace Netgen\Bundle\RemoteMediaBundle\Tests\RemoteMedia\Provider\Cloudinary\Gateway;

use Netgen\Bundle\RemoteMediaBundle\RemoteMedia\Provider\Cloudinary\Gateway\CloudinaryApiGateway;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Serializer\Tests\Fixtures\TraversableDummy;

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
        $options = array(
            'transformations' => array(
                'crop' => array(100, 200)
            ),
            'cloud_name' => 'testcloud',
            'secure' => true
        );

        $this->assertEquals(
            'https://res.cloudinary.com/testcloud/image/upload/test.jpg',
            $this->apiGateway->getVariationUrl($source, $options)
        );
    }

    public function testSearchByTags()
    {
        $options = array(
            'SearchByTags' => true,
        );

        $this->cloudinaryApi
            ->expects($this->once())
            ->method('resources_by_tag')
            ->with(
                'query',
                array(
                    'tags' => true,
                    'context' => true,
                    'resource_type' => 'image',
                    'max_results' => 500
                )
            );

        $this->apiGateway->search('query', $options);
    }

    public function testSearch()
    {
        $apiOptions = array(
            'prefix' => 'query',
            'type' => 'upload',
            'tags' => true,
            'max_results' => 500
        );

        $this->cloudinaryApi->method('resources')->willReturn(new \ArrayObject(array('resources' => array())));

        $this->cloudinaryApi
            ->expects($this->once())
            ->method('resources')
            ->with($apiOptions);

        $this->apiGateway->search('query');
    }

    public function testSearchWithMoreResults()
    {
        $options1 = array(
            'prefix' => 'query',
            'type' => 'upload',
            'tags' => true,
            'max_results' => 500
        );
        $options2 = $options1 + array('next_cursor' => 123);

        $this->cloudinaryApi
            ->method('resources')
            ->willReturnOnConsecutiveCalls(
                new \ArrayObject(
                    array(
                        'resources' => array(),
                        'next_cursor' => 123
                    )
                ),
                new \ArrayObject(
                    array(
                        'resource' => array()
                    )
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
            'max_results' => 500
        );

        $this->cloudinaryApi->method('resources')->willReturn(new \ArrayObject(array('resources' => array())));

        $this->cloudinaryApi
            ->expects($this->once())
            ->method('resources')
            ->with($apiOptions);

        $this->apiGateway->listResources([], 20, 0);
    }

    public function testListResourcesWithMoreResults()
    {
        $options1 = array('max_results' => 500);
        $options2 = $options1 + array('next_cursor' => 123);

        $this->cloudinaryApi
            ->method('resources')
            ->willReturnOnConsecutiveCalls(
                new \ArrayObject(array('resources' => array(), 'next_cursor' => 123)),
                new \ArrayObject(array('resources' => array()))
            );

        $this->cloudinaryApi
            ->expects($this->exactly(2))
            ->method('resources')
            ->withConsecutive([$options1], [$options2]);

        $this->apiGateway->listResources(array(), 0, 20);
    }

    public function testListFolders()
    {
        $this->cloudinaryApi->method('root_folders')->willReturn(new \ArrayObject(array('folders' => array())));
        $this->cloudinaryApi->expects($this->once())->method('root_folders');
        $this->apiGateway->listFolders();
    }

    public function testCountResources()
    {
        $this->cloudinaryApi->method('usage')->willReturn(new \ArrayObject(array('resources' => array())));
        $this->cloudinaryApi->expects($this->once())->method('usage');
        $this->apiGateway->countResources();
    }

    public function testCountResourcesInFolder()
    {
        $options = array('type' => 'upload', 'max_results' => 500, 'prefix' => 'folderName');

        $this->cloudinaryApi->method('resources')->willReturn(new \ArrayObject(array('resources' => array())));

        $this->cloudinaryApi
            ->expects($this->once())
            ->method('resources')
            ->with($options);

        $this->apiGateway->countResourcesInFolder('folderName');
    }

    public function testCountResourcesInFolderWithMoreResults()
    {
        $options1 = array('type' => 'upload', 'max_results' => 500, 'prefix' => 'folderName');
        $options2 = $options1 + array('next_cursor' => 123);

        $this->cloudinaryApi
            ->method('resources')
            ->willReturnOnConsecutiveCalls(
                new \ArrayObject(array('resources' => array(), 'next_cursor' => 123)),
                new \ArrayObject(array('resources' => array()))
            );

        $this->cloudinaryApi
            ->expects($this->exactly(2))
            ->method('resources')
            ->withConsecutive([$options1], [$options2]);

        $this->apiGateway->countResourcesInFolder('folderName');
    }

    public function testGetNotExistingResource()
    {
        $options = array();

        $this->cloudinaryApi
            ->method('resources_by_ids')
            ->willReturn(new \ArrayObject(array()));

        $this->cloudinaryApi
            ->expects($this->once())
            ->method('resources_by_ids')
            ->with(array('test_id'), $options);

        $result = $this->apiGateway->get('test_id', $options);

        $this->assertEquals(
            array(),
            $result
        );
    }

    public function testUpdateResource()
    {
        $options = array();

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
            ->with(array('test_id'));

        $this->apiGateway->delete('test_id');
    }
}
