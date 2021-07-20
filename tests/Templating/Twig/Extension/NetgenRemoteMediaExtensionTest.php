<?php

declare(strict_types=1);

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
     * @var \Netgen\Bundle\RemoteMediaBundle\RemoteMedia\VariationResolver
     */
    protected $variationResolver;

    protected function setUp()
    {
        $this->provider = $this->createMock(RemoteMediaProvider::class);
        $this->translationHelper = $this->createMock(TranslationHelper::class);
        $this->contentTypeService = $this->createMock(ContentTypeService::class);
        $this->variationResolver = $this->createMock(VariationResolver::class);

        $this->extension = new NetgenRemoteMediaExtension(
            $this->provider,
            $this->translationHelper,
            $this->contentTypeService,
            $this->variationResolver,
        );
    }

    public function testName()
    {
        self::assertEquals(
            'netgen_remote_media',
            $this->extension->getName(),
        );
    }

    public function testGetFunctions()
    {
        self::assertNotEmpty($this->extension->getFunctions());

        foreach ($this->extension->getFunctions() as $function) {
            self::assertInstanceOf(\Twig_SimpleFunction::class, $function);
        }
    }

    public function testGetRemoteImageVariation()
    {
        $field = new ContentField(
            [
                'id' => 'some_field',
                'value' => new Value(),
            ],
        );

        $content = new Content(
            [
                'internalFields' => [$field],
                'versionInfo' => new VersionInfo(
                    [
                        'contentInfo' => new ContentInfo(),
                    ],
                ),
            ],
        );

        $variation = new Variation();

        $this->translationHelper->expects(self::once())
            ->method('getTranslatedField')
            ->willReturn($field);

        $this->contentTypeService->expects(self::once())
            ->method('loadContentType')
            ->willReturn(
                new ContentType(
                    [
                        'fieldDefinitions' => [],
                        'identifier' => 'test_identifier',
                    ],
                ),
            );

        $this->provider->expects(self::once())
            ->method('buildVariation')
            ->willReturn($variation);

        self::assertEquals($variation, $this->extension->getRemoteImageVariation($content, 'some_field', 'test_format'));
    }

    public function testGetRemoteVideoTag()
    {
        $field = new ContentField(
            [
                'id' => 'some_field',
                'value' => new Value(),
            ],
        );

        $content = new Content(
            [
                'internalFields' => [$field],
                'versionInfo' => new VersionInfo(
                    [
                        'contentInfo' => new ContentInfo(),
                    ],
                ),
            ],
        );

        $this->translationHelper->expects(self::once())
            ->method('getTranslatedField')
            ->willReturn($field);

        $this->contentTypeService->expects(self::once())
            ->method('loadContentType')
            ->willReturn(
                new ContentType(
                    [
                        'fieldDefinitions' => [],
                        'identifier' => 'test_identifier',
                    ],
                ),
            );

        $this->provider->expects(self::once())
            ->method('generateVideoTag')
            ->willReturn('test_tag');

        self::assertEquals('test_tag', $this->extension->getRemoteVideoTag($content, 'some_field', 'some_format'));
    }

    public function testGetVideoThumbnail()
    {
        $this->provider->expects(self::once())
            ->method('getVideoThumbnail')
            ->willReturn('test_thumbnail');

        self::assertEquals('test_thumbnail', $this->extension->getVideoThumbnail(new Value(), []));
    }

    public function testGetResourceDownloadLink()
    {
        $this->provider->expects(self::once())
            ->method('generateDownloadLink')
            ->willReturn('http://cloudinary.com/some/url/download');

        self::assertEquals('http://cloudinary.com/some/url/download', $this->extension->getResourceDownloadLink(new Value()));
    }

    public function testGetRemoteResource()
    {
        $variation = new Variation();

        $this->provider->expects(self::once())
            ->method('buildVariation')
            ->willReturn($variation);

        self::assertEquals($variation, $this->extension->getRemoteResource(new Value(), 'some_format'));
    }
}
