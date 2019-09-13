<?php

namespace Netgen\Bundle\RemoteMediaBundle\Tests\Templating\Twig\Extension;

use eZ\Publish\API\Repository\ContentTypeService;
use eZ\Publish\API\Repository\Values\Content\ContentInfo;
use eZ\Publish\API\Repository\Values\Content\Field as ContentField;
use eZ\Publish\Core\Helper\TranslationHelper;
use eZ\Publish\Core\Repository\Values\Content\Content;
use eZ\Publish\Core\Repository\Values\Content\VersionInfo;
use eZ\Publish\Core\Repository\Values\ContentType\ContentType;
use Netgen\Bundle\RemoteMediaBundle\Core\FieldType\RemoteMedia\Value;
use Netgen\Bundle\RemoteMediaBundle\Core\FieldType\RemoteMedia\Variation;
use Netgen\Bundle\RemoteMediaBundle\RemoteMedia\Helper;
use Netgen\Bundle\RemoteMediaBundle\RemoteMedia\RemoteMediaProvider;
use Netgen\Bundle\RemoteMediaBundle\RemoteMedia\VariationResolver;
use Netgen\Bundle\RemoteMediaBundle\Templating\Twig\Extension\NetgenRemoteMediaExtension;
use PHPUnit\Framework\TestCase;

class NetgenRemoteMediaExtensionTest extends TestCase
{
    /**
     * @var \Netgen\Bundle\RemoteMediaBundle\Templating\Twig\Extension\NetgenRemoteMediaExtension
     */
    protected $extension;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $provider;
    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $translationHelper;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $contentTypeService;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $helper;

    /**
     * @var \Netgen\Bundle\RemoteMediaBundle\RemoteMedia\VariationResolver
     */
    protected $variationResolver;

    public function setUp()
    {
        $this->provider = $this->createMock(RemoteMediaProvider::class);
        $this->translationHelper = $this->createMock(TranslationHelper::class);
        $this->contentTypeService = $this->createMock(ContentTypeService::class);
        $this->helper = $this->createMock(Helper::class);
        $this->variationResolver = $this->createMock(VariationResolver::class);

        $this->extension = new NetgenRemoteMediaExtension(
            $this->provider,
            $this->translationHelper,
            $this->contentTypeService,
            $this->helper,
            $this->variationResolver
        );
    }

    public function testName()
    {
        $this->assertEquals(
            'netgen_remote_media',
            $this->extension->getName()
        );
    }

    public function testGetFunctions()
    {
        $this->assertNotEmpty($this->extension->getFunctions());

        foreach ($this->extension->getFunctions() as $function) {
            $this->assertInstanceOf(\Twig_SimpleFunction::class, $function);
        }
    }

    public function testGetRemoteImageVariation()
    {
        $field = new ContentField(
            [
                'id' => 'some_field',
                'value' => new Value(),
            ]
        );

        $content = new Content(
            [
                'internalFields' => [$field],
                'versionInfo' => new VersionInfo(
                    [
                        'contentInfo' => new ContentInfo(),
                    ]
                ),
            ]
        );

        $variation = new Variation();

        $this->translationHelper->expects($this->once())
            ->method('getTranslatedField')
            ->will($this->returnValue($field));

        $this->contentTypeService->expects($this->once())
            ->method('loadContentType')
            ->will(
                $this->returnValue(
                    new ContentType(
                        [
                            'fieldDefinitions' => [],
                        ]
                    )
                )
            );

        $this->provider->expects($this->once())
            ->method('buildVariation')
            ->will($this->returnValue($variation));

        $this->assertEquals($variation, $this->extension->getRemoteImageVariation($content, 'some_field', 'test_format'));
    }

    public function testGetRemoteVideoTag()
    {
        $field = new ContentField(
            [
                'id' => 'some_field',
                'value' => new Value(),
            ]
        );

        $content = new Content(
            [
                'internalFields' => [$field],
                'versionInfo' => new VersionInfo(
                    [
                        'contentInfo' => new ContentInfo(),
                    ]
                ),
            ]
        );

        $this->translationHelper->expects($this->once())
            ->method('getTranslatedField')
            ->will($this->returnValue($field));

        $this->contentTypeService->expects($this->once())
            ->method('loadContentType')
            ->will(
                $this->returnValue(
                    new ContentType(
                        [
                            'fieldDefinitions' => [],
                        ]
                    )
                )
            );

        $this->provider->expects($this->once())
            ->method('generateVideoTag')
            ->will($this->returnValue('test_tag'));

        $this->assertEquals('test_tag', $this->extension->getRemoteVideoTag($content, 'some_field', 'some_format'));
    }

    public function testGetVideoThumbnail()
    {
        $this->provider->expects($this->once())
            ->method('getVideoThumbnail')
            ->will($this->returnValue('test_thumbnail'));

        $this->assertEquals('test_thumbnail', $this->extension->getVideoThumbnail(new Value(), []));
    }

    public function testGetResourceDownloadLink()
    {
        $this->provider->expects($this->once())
            ->method('generateDownloadLink')
            ->will($this->returnValue('http://cloudinary.com/some/url/download'));

        $this->assertEquals('http://cloudinary.com/some/url/download', $this->extension->getResourceDownloadLink(new Value(), []));
    }

    public function testGetRemoteResource()
    {
        $variation = new Variation();

        $this->provider->expects($this->once())
            ->method('buildVariation')
            ->will($this->returnValue($variation));

        $this->assertEquals($variation, $this->extension->getRemoteResource(new Value(), 'some_format'));
    }
}
