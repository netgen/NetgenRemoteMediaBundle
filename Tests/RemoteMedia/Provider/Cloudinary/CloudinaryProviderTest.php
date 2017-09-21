<?php

namespace Netgen\Bundle\RemoteMediaBundle\Tests\RemoteMedia\Provider\Cloudinary;

use Netgen\Bundle\RemoteMediaBundle\Core\FieldType\RemoteMedia\Value;
use Netgen\Bundle\RemoteMediaBundle\Core\FieldType\RemoteMedia\Variation;
use Netgen\Bundle\RemoteMediaBundle\Exception\TransformationHandlerFailedException;
use Netgen\Bundle\RemoteMediaBundle\Exception\TransformationHandlerNotFoundException;
use Netgen\Bundle\RemoteMediaBundle\RemoteMedia\Provider\Cloudinary\CloudinaryProvider;
use Netgen\Bundle\RemoteMediaBundle\RemoteMedia\Provider\Cloudinary\Gateway\CloudinaryApiGateway;
use Netgen\Bundle\RemoteMediaBundle\RemoteMedia\Provider\Cloudinary\TransformationHandler\Crop;
use Netgen\Bundle\RemoteMediaBundle\RemoteMedia\Provider\Cloudinary\TransformationHandler\Resize;
use Netgen\Bundle\RemoteMediaBundle\RemoteMedia\Transformation\Registry;
use Netgen\Bundle\RemoteMediaBundle\RemoteMedia\VariationResolver;
use PHPUnit\Framework\TestCase;
use Psr\Log\LoggerInterface;

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

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $logger;

    public function setUp()
    {
        $this->registry = $this->createMock(Registry::class);
        $this->variationResolver = $this->createMock(VariationResolver::class);
        $this->gateway = $this->createMock(CloudinaryApiGateway::class);
        $this->logger = $this->createMock(LoggerInterface::class);

        $this->cloudinaryProvider = new CloudinaryProvider(
            $this->registry,
            $this->variationResolver,
            $this->gateway,
            $this->logger
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
                    'context' => true
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
                    'context' => true
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
                    'context' => true
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
                    'type' => 'upload'
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
                    'type' => 'upload'
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
                )
            );

        $this->cloudinaryProvider->searchResourcesBytag('test/something');
    }

    public function testGetRemoteResource()
    {
        $options = array(
            'resource_type' => 'image',
            'max_results' => 1,
            'tags' => true,
            'context' => true,
        );

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
            ->with('testResourceId', $options);

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
        $options = array(
            'resource_type' => 'video',
            'max_results' => 1,
            'tags' => true,
            'context' => true,
        );

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
            ->with('testResourceId', $options);

        $value = $this->cloudinaryProvider->getRemoteResource('testResourceId', 'video');

        $this->assertInstanceOf(Value::class, $value);
        $this->assertEquals(
            Value::TYPE_VIDEO,
            $value->mediaType
        );
    }

    public function testGetRemoteDocument()
    {
        $options = array(
            'resource_type' => 'image',
            'max_results' => 1,
            'tags' => true,
            'context' => true,
        );

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
            ->with('testResourceId', $options);

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
            'controls' => true,
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
            'controls' => true,
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

    public function testGetVideoTagWithProvidedVariationName()
    {
        $options = array(
            'controls' => true,
            'fallback_content' => 'Your browser does not support HTML5 video tags'
        );

        $cropConfig = array(
            'x' => 200,
            'y' => 100,
            'w' => 50,
            'h' => 50,
        );

        $transformations = array(
            'crop' => $cropConfig,
            'resize' => array(),
            'non_existing_transformation' => array(),
        );

        $variations = array(
            'test_variation' => array(
                'transformations' => $transformations,
            ),
        );

        $cropOptions = array(
            'x' => $cropConfig['x'],
            'y' => $cropConfig['y'],
            'width' => $cropConfig['w'],
            'height' => (int)$cropConfig['h'],
            'crop' => 'crop',
        );

        $variationConfig = array(
            'secure' => true,
            'transformation' => array(
                $cropOptions
            ),
        );

        $value = new Value();
        $value->resourceId = 'testResourceId';
        $value->variations = $transformations;

        $this->variationResolver
            ->expects($this->once())
            ->method('getVariationsForContentType')
            ->with('test_content_type')
            ->willReturn($variations);

        $cropHandler = $this->createMock(Crop::class);
        $resizeHandler = $this->createMock(Resize::class);

        $cropHandler
            ->expects($this->once())
            ->method('process')
            ->with($value, 'test_variation', $cropConfig)
            ->willReturn($cropOptions);

        $resizeHandler
            ->expects($this->once())
            ->method('process')
            ->with($value, 'test_variation', array())
            ->willThrowException(new TransformationHandlerFailedException(Resize::class));

        $this->registry
            ->expects($this->exactly(3))
            ->method('getHandler')
            ->withConsecutive(
                ['crop', 'cloudinary'],
                ['resize', 'cloudinary']
            )
            ->willReturnOnConsecutiveCalls(
                $cropHandler,
                $resizeHandler
            );

        $this->registry
            ->expects($this->at(2))
            ->method('getHandler')
            ->with('non_existing_transformation', 'cloudinary')
            ->willThrowException(new TransformationHandlerNotFoundException('cloudinary', 'non_existing_transformation'));

        $this->gateway
            ->expects($this->once())
            ->method('getVideoTag')
            ->with($value->resourceId, $options + $variationConfig)
            ->willReturn('video_tag')
        ;

        $this->logger
            ->expects($this->exactly(2))
            ->method('error');

        $this->assertEquals('video_tag', $this->cloudinaryProvider->generateVideoTag($value, 'test_content_type', 'test_variation'));
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
                'some/path',
                $options
            );

        $value = $this->cloudinaryProvider->upload('some/path', 'name', array('overwrite' => true));

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

    public function testBuildVariationWithProvidedVariationName()
    {
        $options = array(
            'transformation' => array(),
            'secure' => true,
        );

        $smallVariation = array(
            'x' => 10,
            'y' => 10,
            'w' => 300,
            'h' => 200
        );

        $value = new Value(
            array(
                'resourceId' => 'testId',
                'url' => 'http://cloudinary.com/some/url',
                'secure_url' => 'https://cloudinary.com/some/url',
                'variations' => array(
                    'small' => $smallVariation,
                )
            )
        );

        $variations = array(
            'small' => array(
                'transformations' => array(),
            ),
        );

        $this->variationResolver
            ->expects($this->once())
            ->method('getVariationsForContentType')
            ->with('test_content_type')
            ->willReturn($variations);

        $this->gateway
            ->expects($this->once())
            ->method('getVariationUrl')
            ->with($value->resourceId, $options)
            ->willReturn('https://cloudinary.com/c_fit,w_200,h_200/testId')
        ;

        $variation = $this->cloudinaryProvider->buildVariation(
            $value,
            'test_content_type',
            'small'
        );

        $this->assertInstanceOf(Variation::class, $variation);
        $this->assertEquals(
            'https://cloudinary.com/c_fit,w_200,h_200/testId',
            $variation->url
        );
    }
}
