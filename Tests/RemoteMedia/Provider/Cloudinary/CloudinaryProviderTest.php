<?php

namespace Netgen\Bundle\RemoteMediaBundle\Tests\RemoteMedia\Provider\Cloudinary;

use Netgen\Bundle\RemoteMediaBundle\Core\FieldType\RemoteMedia\Value;
use Netgen\Bundle\RemoteMediaBundle\Core\FieldType\RemoteMedia\Variation;
use Netgen\Bundle\RemoteMediaBundle\RemoteMedia\Provider\Cloudinary\CloudinaryProvider;
use Netgen\Bundle\RemoteMediaBundle\RemoteMedia\Provider\Cloudinary\Gateway\CloudinaryApiGateway;
use Netgen\Bundle\RemoteMediaBundle\RemoteMedia\Transformation\Registry;
use Netgen\Bundle\RemoteMediaBundle\RemoteMedia\VariationResolver;
use org\bovigo\vfs\vfsStream;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpFoundation\File\Exception\FileNotFoundException;

class CloudinaryProviderTest extends TestCase
{
    /**
     * @var \Netgen\Bundle\RemoteMediaBundle\RemoteMedia\Provider\Cloudinary\CloudinaryProvider
     */
    protected $cloudinaryProvider;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $registry;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $variationResolver;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $gateway;

    public function setUp()
    {
        $this->registry = $this->createMock(Registry::class);
        $this->variationResolver = $this->createMock(VariationResolver::class);
        $this->gateway = $this->createMock(CloudinaryApiGateway::class);

        $this->cloudinaryProvider = new CloudinaryProvider(
            $this->registry,
            $this->variationResolver,
            $this->gateway
        );
    }

    public function testIdentifier()
    {
        $this->assertEquals(
            'cloudinary',
            $this->cloudinaryProvider->getIdentifier()
        );
    }

    public function testSupportsContentBrowser()
    {
        $this->assertEquals(
            true,
            $this->cloudinaryProvider->supportsContentBrowser()
        );
    }

    public function testSupportsFolders()
    {
        $this->assertEquals(
            true,
            $this->cloudinaryProvider->supportsFolders()
        );
    }

    public function testListResources()
    {
        $this->gateway->method('listResources')->willReturn(array());

        $this->gateway
            ->expects($this->once())
            ->method('listResources')
            ->with(
                array(
                    'tags' => true,
                    'context' => true,
                    'resource_type' => 'image'
                ),
                10,
                0
            );

        $this->cloudinaryProvider->listResources();
    }

    public function testListResourcesWithLimit()
    {
        $this->gateway->method('listResources')->willReturn(array());

        $this->gateway
            ->expects($this->once())
            ->method('listResources')
            ->with(
                array(
                    'tags' => true,
                    'context' => true,
                    'resource_type' => 'image'
                ),
                20,
                0
            );

        $this->cloudinaryProvider->listResources(20);
    }

    public function testListResourcesWithLimitAndOffset()
    {
        $this->gateway->method('listResources')->willReturn(array());

        $this->gateway
            ->expects($this->once())
            ->method('listResources')
            ->with(
                array(
                    'tags' => true,
                    'context' => true,
                    'resource_type' => 'image'
                ),
                20,
                5
            );

        $this->cloudinaryProvider->listResources(20, 5);
    }

    public function testCountResources()
    {
        $this->gateway
            ->expects($this->once())
            ->method('countResources');

        $this->cloudinaryProvider->countResources();
    }

    public function testListFolders()
    {
        $this->gateway
            ->expects($this->once())
            ->method('listFolders');

        $this->cloudinaryProvider->listFolders();
    }

    public function testCountResourcesInFolder()
    {
        $this->gateway
            ->expects($this->once())
            ->method('countResourcesInFolder');

        $this->cloudinaryProvider->countResourcesInFolder('testFolder');
    }

    public function testSearchResources()
    {
        $this->gateway
            ->expects($this->once())
            ->method('search')
            ->with(
                'queryTerm',
                array(
                    'SearchByTags' => false,
                    'type' => 'upload',
                    'resource_type' => 'image'
                ),
                10,
                0
            );

        $this->cloudinaryProvider->searchResources('queryTerm');
    }

    public function testSearchResourcesWithLimitAndOffset()
    {
        $this->gateway
            ->expects($this->once())
            ->method('search')
            ->with(
                'queryTerm',
                array(
                    'SearchByTags' => false,
                    'type' => 'upload',
                    'resource_type' => 'image'
                ),
                25,
                50
            );

        $this->cloudinaryProvider->searchResources('queryTerm', 25, 50);
    }

    public function testSearchResourcesByTag()
    {
        $this->gateway
            ->expects($this->once())
            ->method('search')
            ->with(
                'queryTerm',
                array(
                    'SearchByTags' => true,
                    'resource_type' => 'image'
                )
            );

        $this->cloudinaryProvider->searchResourcesBytag('queryTerm');
    }

    public function testSearchResourcesByTagWithEncoding()
    {
        $this->gateway
            ->expects($this->once())
            ->method('search')
            ->with(
                'test%2Fsomething',
                array(
                    'SearchByTags' => true,
                    'resource_type' => 'image'
                )
            );

        $this->cloudinaryProvider->searchResourcesBytag('test/something');
    }

    public function testGetEmptyResourceId()
    {
        $this->gateway
            ->expects($this->never())
            ->method('get');

        $value = $this->cloudinaryProvider->getRemoteResource('', 'image');

        $this->assertInstanceOf(Value::class, $value);
        $this->assertEquals(
            null,
            $value->resourceId
        );
    }

    public function testGetRemoteResource()
    {
        $this->gateway->method('get')->willReturn(
            array(
                'public_id' => 'testResourceId',
                'url' => 'http://some.url/path',
                'secure_url' => 'https://some.url/path',
                'bytes' => 1024,
                'resource_type' => 'image'
            )
        );

        $this->gateway
            ->expects($this->once())
            ->method('get')
            ->with('testResourceId', 'image');

        $value = $this->cloudinaryProvider->getRemoteResource('testResourceId', 'image');

        $this->assertInstanceOf(Value::class, $value);
        $this->assertEquals(
            'testResourceId',
            $value->resourceId
        );
        $this->assertEquals(
            'http://some.url/path',
            $value->url
        );
        $this->assertEquals(
            'https://some.url/path',
            $value->secure_url
        );
        $this->assertEquals(
            1024,
            $value->size
        );
        $this->assertEquals(
            Value::TYPE_IMAGE,
            $value->mediaType
        );
    }

    public function testGetRemoteVideo()
    {
        $this->gateway->method('get')->willReturn(
            array(
                'public_id' => 'testResourceId',
                'url' => 'http://some.url/path',
                'secure_url' => 'https://some.url/path',
                'bytes' => 1024,
                'resource_type' => 'video'
            )
        );

        $this->gateway
            ->expects($this->once())
            ->method('get')
            ->with('testResourceId', 'video');

        $value = $this->cloudinaryProvider->getRemoteResource('testResourceId', 'video');

        $this->assertInstanceOf(Value::class, $value);
        $this->assertEquals(
            Value::TYPE_VIDEO,
            $value->mediaType
        );
    }

    public function testGetRemoteDocument()
    {
        $this->gateway->method('get')->willReturn(
            array(
                'public_id' => 'testResourceId',
                'url' => 'http://some.url/path',
                'secure_url' => 'https://some.url/path',
                'bytes' => 1024,
                'resource_type' => 'image',
                'format' => 'pdf'
            )
        );

        $this->gateway
            ->expects($this->once())
            ->method('get')
            ->with('testResourceId', 'image');

        $value = $this->cloudinaryProvider->getRemoteResource('testResourceId', 'image');

        $this->assertInstanceOf(Value::class, $value);
        $this->assertEquals(
            Value::TYPE_OTHER,
            $value->mediaType
        );
    }

    public function testAddTag()
    {
        $this->gateway
            ->expects($this->once())
            ->method('addTag')
            ->with('testResourceId', 'testTag')
        ;

        $this->cloudinaryProvider->addTagToResource('testResourceId', 'testTag');
    }

    public function testRemoveTag()
    {
        $this->gateway
            ->expects($this->once())
            ->method('removeTag')
            ->with('testResourceId', 'testTag')
        ;

        $this->cloudinaryProvider->removeTagFromResource('testResourceId', 'testTag');
    }

    public function testUpdateResourceContext()
    {
        $options = array(
            'context' => array('caption' => 'test_caption'),
            'resource_type' => 'image'
        );

        $this->gateway
            ->expects($this->once())
            ->method('update')
            ->with('testResourceId', $options);

        $this->cloudinaryProvider->updateResourceContext('testResourceId', 'image', array('caption' => 'test_caption'));
    }

    public function testGetVideoThumbnail()
    {
        $options = array(
            'start_offset' => 'auto',
            'resource_type' => 'video',
            'crop' =>'fit',
            'width' => 320,
            'height' => 240
        );

        $this->gateway
            ->expects($this->once())
            ->method('getVideoThumbnail')
            ->with('testResourceId', $options);

        $value = new Value();
        $value->resourceId = 'testResourceId';

        $this->cloudinaryProvider->getVideoThumbnail($value);
    }

    public function testVideoThumbnailWithProvidedOptions()
    {
        $options = array(
            'start_offset' => 'auto',
            'resource_type' => 'video',
            'crop' =>'fill',
            'width' => 200,
            'height' => 200
        );

        $this->gateway
            ->expects($this->once())
            ->method('getVideoThumbnail')
            ->with('testResourceId', $options);

        $value = new Value();
        $value->resourceId = 'testResourceId';

        $this->cloudinaryProvider->getVideoThumbnail($value, array('crop' => 'fill', 'width' => 200, 'height' => 200));
    }

    public function testGetVideoTag()
    {
        $options = array(
            'fallback_content' => 'Your browser does not support HTML5 video tags'
        );

        $this->gateway
            ->expects($this->once())
            ->method('getVideoTag')
            ->with('testResourceId', $options);

        $value = new Value();
        $value->resourceId = 'testResourceId';

        $this->cloudinaryProvider->generateVideoTag($value, 'test_content_type');
    }

    public function testGetVideoTagWithProvidedVariation()
    {
        $options = array(
            'fallback_content' => 'Your browser does not support HTML5 video tags'
        );

        $variationConfig = array(
            'crop' => 'fit',
            'width' => 200
        );

        $this->gateway
            ->expects($this->once())
            ->method('getVideoTag')
            ->with('testResourceId', $options + $variationConfig);

        $value = new Value();
        $value->resourceId = 'testResourceId';

        $this->cloudinaryProvider->generateVideoTag($value, 'test_content_type', $variationConfig);
    }

    public function testGenerateDownloadLink()
    {
        $options = array(
            'type' => 'upload',
            'resource_type' => 'image',
            'flags' => 'attachment'
        );

        $value = new Value(
            array(
                'resourceId' => 'testResourceId',
                'metaData' => array('type' => 'upload', 'resource_type' => 'image')
            )
        );

        $this->gateway
            ->expects($this->once())
            ->method('getDownloadLink')
            ->with('testResourceId', $options);

        $this->cloudinaryProvider->generateDownloadLink($value);
    }

    public function testDeleteResource()
    {
        $this->gateway
            ->expects($this->once())
            ->method('delete')
            ->with('testResourceId');

        $this->cloudinaryProvider->deleteResource('testResourceId');
    }

    public function testUpload()
    {
        $options = array(
            'public_id' => 'name',
            'overwrite' => true,
            'invalidate' => true,
            'discard_original_filename' => true,
            'context' => array(
                'alt' => '',
                'caption' => '',
            ),
            'resource_type' => 'auto'
        );

        $root = vfsStream::setup('some');
        $file = vfsStream::newFile('path')->at($root);

        $this->gateway->method('upload')->willReturn(
            array(
                'public_id' => 'name',
                'url' => 'http://some.url/path',
                'secure_url' => 'https://some.url/path',
                'bytes' => 1024,
                'resource_type' => 'image'
            )
        );

        $this->gateway
            ->expects($this->once())
            ->method('upload')
            ->with(
                $file->url(),
                $options
            );

        $value = $this->cloudinaryProvider->upload($file->url(), 'name', array('overwrite' => true));

        $this->assertInstanceOf(Value::class, $value);

        $this->assertEquals(
            'name',
            $value->resourceId
        );
        $this->assertEquals(
            'http://some.url/path',
            $value->url
        );
        $this->assertEquals(
            'https://some.url/path',
            $value->secure_url
        );
        $this->assertEquals(
            1024,
            $value->size
        );
        $this->assertEquals(
            Value::TYPE_IMAGE,
            $value->mediaType
        );
    }

    public function testUploadWithExtension()
    {
        $options = array(
            'public_id' => 'file.zip',
            'overwrite' => true,
            'invalidate' => true,
            'discard_original_filename' => true,
            'context' => array(
                'alt' => '',
                'caption' => '',
            ),
            'resource_type' => 'auto'
        );

        $root = vfsStream::setup('some');
        $file = vfsStream::newFile('file.zip')->at($root);

        $this->gateway->method('upload')->willReturn(
            array(
                'public_id' => 'file.zip',
                'url' => 'http://some.url/file.zip',
                'secure_url' => 'https://some.url/file.zip',
                'bytes' => 1024,
                'resource_type' => 'other'
            )
        );

        $this->gateway
            ->expects($this->once())
            ->method('upload')
            ->with(
                $file->url(),
                $options
            );

        $value = $this->cloudinaryProvider->upload($file->url(), 'file', array('overwrite' => true));

        $this->assertInstanceOf(Value::class, $value);

        $this->assertEquals(
            'file.zip',
            $value->resourceId
        );
        $this->assertEquals(
            'http://some.url/file.zip',
            $value->url
        );
        $this->assertEquals(
            'https://some.url/file.zip',
            $value->secure_url
        );
        $this->assertEquals(
            1024,
            $value->size
        );
        $this->assertEquals(
            Value::TYPE_OTHER,
            $value->mediaType
        );
    }

    public function testUploadNoFile()
    {
        $this->expectException(FileNotFoundException::class);

        $this->cloudinaryProvider->upload('some/path', 'name', array('overwrite' => true));
    }

    public function testBuildVariation()
    {
        $value = new Value(
            array(
                'resourceId' => 'testId',
                'url' => 'http://cloudinary.com/some/url',
                'secure_url' => 'https://cloudinary.com/some/url',
                'variations' => array(
                    'small' => array(
                        'x' => 10,
                        'y' => 10,
                        'w' => 300,
                        'h' => 200
                    )
                )
            )
        );

        $variation = $this->cloudinaryProvider->buildVariation($value, 'test_content_type', '');

        $this->assertInstanceOf(Variation::class, $variation);
        $this->assertEquals(
            $value->secure_url,
            $variation->url
        );
    }

    public function testBuildVariationWithProvidedConfiguration()
    {
        $value = new Value(
            array(
                'resourceId' => 'testId',
                'url' => 'http://cloudinary.com/some/url',
                'secure_url' => 'https://cloudinary.com/some/url',
                'variations' => array(
                    'small' => array(
                        'x' => 10,
                        'y' => 10,
                        'w' => 300,
                        'h' => 200
                    )
                )
            )
        );

        $this->gateway->method('getVariationUrl')->willReturn('https://cloudinary.com/c_fit,w_200,h_200/testId');

        $variation = $this->cloudinaryProvider->buildVariation(
            $value,
            'test_content_type',
            array('crop' => 'fit', 'width' => 200, 'height' => 200)
        );

        $this->assertInstanceOf(Variation::class, $variation);
        $this->assertEquals(
            'https://cloudinary.com/c_fit,w_200,h_200/testId',
            $variation->url
        );
    }
}
