<?php

declare(strict_types=1);

namespace Netgen\Bundle\RemoteMediaBundle\Tests\RemoteMedia\Provider\Cloudinary;

use Cloudinary\Api\Response;
use Netgen\Bundle\RemoteMediaBundle\Core\FieldType\RemoteMedia\Value;
use Netgen\Bundle\RemoteMediaBundle\Core\FieldType\RemoteMedia\Variation;
use Netgen\Bundle\RemoteMediaBundle\RemoteMedia\Provider\Cloudinary\CloudinaryProvider;
use Netgen\Bundle\RemoteMediaBundle\RemoteMedia\Provider\Cloudinary\Gateway\CloudinaryApiGateway;
use Netgen\Bundle\RemoteMediaBundle\RemoteMedia\Provider\Cloudinary\Search\Query;
use Netgen\Bundle\RemoteMediaBundle\RemoteMedia\Provider\Cloudinary\Search\Result;
use Netgen\Bundle\RemoteMediaBundle\RemoteMedia\Transformation\Registry;
use Netgen\Bundle\RemoteMediaBundle\RemoteMedia\UploadFile;
use Netgen\Bundle\RemoteMediaBundle\RemoteMedia\VariationResolver;
use org\bovigo\vfs\vfsStream;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpFoundation\File\Exception\FileNotFoundException;
use function json_encode;

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

    protected function setUp()
    {
        $this->registry = $this->createMock(Registry::class);
        $this->variationResolver = $this->createMock(VariationResolver::class);
        $this->gateway = $this->createMock(CloudinaryApiGateway::class);

        $this->cloudinaryProvider = new CloudinaryProvider(
            $this->registry,
            $this->variationResolver,
            $this->gateway,
            false
        );
    }

    public function testIdentifier()
    {
        self::assertEquals(
            'cloudinary',
            $this->cloudinaryProvider->getIdentifier()
        );
    }

    public function testSupportsContentBrowser()
    {
        self::assertFalse(
            $this->cloudinaryProvider->supportsContentBrowser()
        );
    }

    public function testSupportsFolders()
    {
        self::assertTrue(
            $this->cloudinaryProvider->supportsFolders()
        );
    }

    public function testCountResources()
    {
        $this->gateway
            ->expects(self::once())
            ->method('countResources')
            ->willReturn(0);

        $this->cloudinaryProvider->countResources();
    }

    public function testListFolders()
    {
        $this->gateway
            ->expects(self::once())
            ->method('listFolders')
            ->willReturn([]);

        $this->cloudinaryProvider->listFolders();
    }

    public function testCountResourcesInFolder()
    {
        $this->gateway
            ->expects(self::once())
            ->method('countResourcesInFolder')
            ->willReturn(0);

        $this->cloudinaryProvider->countResourcesInFolder('testFolder');
    }

    public function testSearchResources()
    {
        $query = new Query('query', 'image', 0);

        $this->gateway
            ->expects(self::once())
            ->method('search')
            ->with($query)
            ->willReturn(
                Result::fromResponse(new Response($this->getSearchResponse()))
            );

        $this->cloudinaryProvider->searchResources($query);
    }

    public function testSearchResourcesWithLimitAndOffset()
    {
        $query = new Query('query', 'image', 25, null, null, '823b');

        $this->gateway
            ->expects(self::once())
            ->method('search')
            ->with($query)
            ->willReturn(
                Result::fromResponse(new Response($this->getSearchResponse()))
            );

        $this->cloudinaryProvider->searchResources($query);
    }

    public function testSearchResourcesByTag()
    {
        $query = new Query('', 'image', 25, null, 'tag');

        $this->gateway
            ->expects(self::once())
            ->method('search')
            ->with($query)
            ->willReturn(
                Result::fromResponse(new Response($this->getSearchResponse()))
            );

        $this->cloudinaryProvider->searchResources($query);
    }

    public function testGetEmptyResourceId()
    {
        $this->gateway
            ->expects(self::never())
            ->method('get');

        $value = $this->cloudinaryProvider->getRemoteResource('', 'image');

        self::assertInstanceOf(Value::class, $value);
        self::assertNull(
            $value->resourceId
        );
    }

    public function testGetRemoteResource()
    {
        $this->gateway->method('get')->willReturn(
            [
                'public_id' => 'testResourceId',
                'url' => 'http://some.url/path',
                'secure_url' => 'https://some.url/path',
                'bytes' => 1024,
                'resource_type' => 'image',
            ]
        );

        $this->gateway
            ->expects(self::once())
            ->method('get')
            ->with('testResourceId', 'image');

        $value = $this->cloudinaryProvider->getRemoteResource('testResourceId', 'image');

        self::assertInstanceOf(Value::class, $value);
        self::assertEquals(
            'testResourceId',
            $value->resourceId
        );
        self::assertEquals(
            'http://some.url/path',
            $value->url
        );
        self::assertEquals(
            'https://some.url/path',
            $value->secure_url
        );
        self::assertEquals(
            1024,
            $value->size
        );
        self::assertEquals(
            Value::TYPE_IMAGE,
            $value->mediaType
        );
    }

    public function testGetRemoteVideo()
    {
        $this->gateway->method('get')->willReturn(
            [
                'public_id' => 'testResourceId',
                'url' => 'http://some.url/path',
                'secure_url' => 'https://some.url/path',
                'bytes' => 1024,
                'resource_type' => 'video',
            ]
        );

        $this->gateway
            ->expects(self::once())
            ->method('get')
            ->with('testResourceId', 'video');

        $value = $this->cloudinaryProvider->getRemoteResource('testResourceId', 'video');

        self::assertInstanceOf(Value::class, $value);
        self::assertEquals(
            Value::TYPE_VIDEO,
            $value->mediaType
        );
    }

    public function testGetRemoteDocument()
    {
        $this->gateway->method('get')->willReturn(
            [
                'public_id' => 'testResourceId',
                'url' => 'http://some.url/path',
                'secure_url' => 'https://some.url/path',
                'bytes' => 1024,
                'resource_type' => 'image',
                'format' => 'pdf',
            ]
        );

        $this->gateway
            ->expects(self::once())
            ->method('get')
            ->with('testResourceId', 'image');

        $value = $this->cloudinaryProvider->getRemoteResource('testResourceId', 'image');

        self::assertInstanceOf(Value::class, $value);
        self::assertEquals(
            Value::TYPE_OTHER,
            $value->mediaType
        );
    }

    public function testAddTag()
    {
        $this->gateway
            ->expects(self::once())
            ->method('addTag')
            ->with('testResourceId', 'image', 'testTag');

        $this->cloudinaryProvider->addTagToResource('testResourceId', 'testTag', 'image');
    }

    public function testRemoveTag()
    {
        $this->gateway
            ->expects(self::once())
            ->method('removeTag')
            ->with('testResourceId', 'image', 'testTag');

        $this->cloudinaryProvider->removeTagFromResource('testResourceId', 'testTag', 'image');
    }

    public function testUpdateResourceContext()
    {
        $options = [
            'context' => ['caption' => 'test_caption'],
        ];

        $this->gateway
            ->expects(self::once())
            ->method('update')
            ->with('testResourceId', 'image', $options);

        $this->cloudinaryProvider->updateResourceContext('testResourceId', 'image', ['caption' => 'test_caption']);
    }

    public function testGetVideoThumbnail()
    {
        $options = [
            'start_offset' => 'auto',
            'resource_type' => 'video',
        ];

        $this->gateway
            ->expects(self::once())
            ->method('getVideoThumbnail')
            ->with('testResourceId', $options)
            ->willReturn('');

        $value = new Value();
        $value->resourceId = 'testResourceId';

        $this->cloudinaryProvider->getVideoThumbnail($value);
    }

    public function testVideoThumbnailWithProvidedOptions()
    {
        $options = [
            'start_offset' => 'auto',
            'resource_type' => 'video',
            'crop' => 'fill',
            'width' => 200,
            'height' => 200,
        ];

        $this->gateway
            ->expects(self::once())
            ->method('getVideoThumbnail')
            ->with('testResourceId', $options)
            ->willReturn('');

        $value = new Value();
        $value->resourceId = 'testResourceId';

        $this->cloudinaryProvider->getVideoThumbnail($value, ['crop' => 'fill', 'width' => 200, 'height' => 200]);
    }

    public function testGetVideoTag()
    {
        $options = [
            'fallback_content' => 'Your browser does not support HTML5 video tags',
            'controls' => true,
            'poster' => [],
        ];

        $this->gateway
            ->expects(self::once())
            ->method('getVideoTag')
            ->with('testResourceId', $options)
            ->willReturn('');

        $value = new Value();
        $value->resourceId = 'testResourceId';

        $this->cloudinaryProvider->generateVideoTag($value, 'test_content_type', []);
    }

    public function testGetVideoTagWithProvidedVariation()
    {
        $variationConfig = [
            'crop' => 'fit',
            'width' => 200,
        ];

        $options = [
            'fallback_content' => 'Your browser does not support HTML5 video tags',
            'controls' => true,
            'poster' => $variationConfig,
        ];

        $this->gateway
            ->expects(self::once())
            ->method('getVideoTag')
            ->with('testResourceId', $options + $variationConfig)
            ->willReturn('');

        $value = new Value();
        $value->resourceId = 'testResourceId';

        $this->cloudinaryProvider->generateVideoTag($value, 'test_content_type', $variationConfig);
    }

    public function testGenerateDownloadLink()
    {
        $options = [
            'type' => 'upload',
            'resource_type' => 'image',
            'flags' => 'attachment',
            'secure' => true,
        ];

        $value = new Value(
            [
                'resourceId' => 'testResourceId',
                'resourceType' => 'image',
                'type' => 'upload',
            ]
        );

        $this->gateway
            ->expects(self::once())
            ->method('getDownloadLink')
            ->with('testResourceId', 'image', $options)
            ->willReturn('test.com/download-link');

        self::assertEquals(
            'test.com/download-link',
            $this->cloudinaryProvider->generateDownloadLink($value)
        );
    }

    public function testDeleteResource()
    {
        $this->gateway
            ->expects(self::once())
            ->method('delete')
            ->with('testResourceId');

        $this->cloudinaryProvider->deleteResource('testResourceId');
    }

    public function testUpload()
    {
        $options = [
            'public_id' => 'filename',
            'overwrite' => true,
            'invalidate' => true,
            'discard_original_filename' => true,
            'context' => [
                'alt' => '',
                'caption' => '',
            ],
            'resource_type' => 'auto',
            'tags' => [],
        ];

        $root = vfsStream::setup('some');
        $file = vfsStream::newFile('filename')->at($root);

        $uploadFile = UploadFile::fromUri($file->url());

        $this->gateway->method('upload')->willReturn(
            [
                'public_id' => 'filename',
                'url' => 'http://some.url/filename',
                'secure_url' => 'https://some.url/filename',
                'bytes' => 1024,
                'resource_type' => 'image',
            ]
        );

        $this->gateway
            ->expects(self::once())
            ->method('upload')
            ->with(
                $uploadFile->uri(),
                $options
            );

        $value = $this->cloudinaryProvider->upload($uploadFile, ['overwrite' => true]);

        self::assertInstanceOf(Value::class, $value);

        self::assertEquals(
            'filename',
            $value->resourceId
        );
        self::assertEquals(
            'http://some.url/filename',
            $value->url
        );
        self::assertEquals(
            'https://some.url/filename',
            $value->secure_url
        );
        self::assertEquals(
            1024,
            $value->size
        );
        self::assertEquals(
            Value::TYPE_IMAGE,
            $value->mediaType
        );
    }

    public function testUploadWithExtension()
    {
        $options = [
            'public_id' => 'file.zip',
            'overwrite' => true,
            'invalidate' => true,
            'discard_original_filename' => true,
            'context' => [
                'alt' => '',
                'caption' => '',
            ],
            'resource_type' => 'auto',
            'tags' => [],
        ];

        $root = vfsStream::setup('some');
        $file = vfsStream::newFile('file.zip')->at($root);

        $this->gateway->method('upload')->willReturn(
            [
                'public_id' => 'file.zip',
                'url' => 'http://some.url/file.zip',
                'secure_url' => 'https://some.url/file.zip',
                'bytes' => 1024,
                'resource_type' => 'other',
            ]
        );

        $this->gateway
            ->expects(self::once())
            ->method('upload')
            ->with(
                $file->url(),
                $options
            );

        $uploadFile = UploadFile::fromUri($file->url());

        $value = $this->cloudinaryProvider->upload($uploadFile, ['overwrite' => true]);

        self::assertInstanceOf(Value::class, $value);

        self::assertEquals(
            'file.zip',
            $value->resourceId
        );
        self::assertEquals(
            'http://some.url/file.zip',
            $value->url
        );
        self::assertEquals(
            'https://some.url/file.zip',
            $value->secure_url
        );
        self::assertEquals(
            1024,
            $value->size
        );
        self::assertEquals(
            Value::TYPE_OTHER,
            $value->mediaType
        );
    }

    public function testUploadNoFile()
    {
        $this->expectException(FileNotFoundException::class);

        $uploadFile = UploadFile::fromUri('/some/path.jpg');

        $this->cloudinaryProvider->upload($uploadFile, ['overwrite' => true]);
    }

    public function testBuildVariation()
    {
        $value = new Value(
            [
                'resourceId' => 'testId',
                'url' => 'http://cloudinary.com/some/url',
                'secure_url' => 'https://cloudinary.com/some/url',
                'variations' => [
                    'small' => [
                        'x' => 10,
                        'y' => 10,
                        'w' => 300,
                        'h' => 200,
                    ],
                ],
            ]
        );

        $variation = $this->cloudinaryProvider->buildVariation($value, 'test_content_type', '');

        self::assertInstanceOf(Variation::class, $variation);
        self::assertEquals(
            $value->secure_url,
            $variation->url
        );
    }

    public function testBuildVariationWithProvidedConfiguration()
    {
        $value = new Value(
            [
                'resourceId' => 'testId',
                'url' => 'http://cloudinary.com/some/url',
                'secure_url' => 'https://cloudinary.com/some/url',
                'variations' => [
                    'small' => [
                        'x' => 10,
                        'y' => 10,
                        'w' => 300,
                        'h' => 200,
                    ],
                ],
            ]
        );

        $this->gateway->method('getVariationUrl')->willReturn('https://cloudinary.com/c_fit,w_200,h_200/testId');

        $variation = $this->cloudinaryProvider->buildVariation(
            $value,
            'test_content_type',
            ['crop' => 'fit', 'width' => 200, 'height' => 200]
        );

        self::assertInstanceOf(Variation::class, $variation);
        self::assertEquals(
            'https://cloudinary.com/c_fit,w_200,h_200/testId',
            $variation->url
        );
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
