<?php

declare(strict_types=1);

namespace Netgen\Bundle\RemoteMediaBundle\Tests\OpenGraph\Handler\RemoteMediaHandler;

use eZ\Publish\API\Repository\Values\Content\ContentInfo;
use eZ\Publish\API\Repository\Values\Content\Field;
use eZ\Publish\Core\Helper\FieldHelper;
use eZ\Publish\Core\Helper\TranslationHelper;
use eZ\Publish\Core\Repository\ContentTypeService;
use eZ\Publish\Core\Repository\Values\Content\Content;
use eZ\Publish\Core\Repository\Values\Content\VersionInfo;
use eZ\Publish\Core\Repository\Values\ContentType\ContentType;
use Netgen\Bundle\OpenGraphBundle\Handler\HandlerInterface;
use Netgen\Bundle\OpenGraphBundle\Tests\Handler\FieldType\HandlerBaseTest;
use Netgen\Bundle\RemoteMediaBundle\Core\FieldType\RemoteMedia\Value;
use Netgen\Bundle\RemoteMediaBundle\Core\FieldType\RemoteMedia\Variation;
use Netgen\Bundle\RemoteMediaBundle\OpenGraph\Handler\RemoteMediaHandler;
use Netgen\Bundle\RemoteMediaBundle\RemoteMedia\RemoteMediaProvider;
use Psr\Log\NullLogger;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestStack;

class RemoteMediaHandlerTest extends HandlerBaseTest
{
    /**
     * @var \Netgen\Bundle\RemoteMediaBundle\OpenGraph\Handler\RemoteMediaHandler
     */
    protected $remoteMediaHandler;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $provider;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $contentTypeService;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $requestStack;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $logger;

    protected function setUp()
    {
        $this->fieldHelper = $this->getMockBuilder(FieldHelper::class)
            ->disableOriginalConstructor()
            ->setMethods(['isFieldEmpty'])
            ->getMock();

        $this->translationHelper = $this->getMockBuilder(TranslationHelper::class)
            ->disableOriginalConstructor()
            ->setMethods(['getTranslatedField'])
            ->getMock();

        $this->contentTypeService = $this->getMockBuilder(ContentTypeService::class)
            ->disableOriginalConstructor()
            ->setMethods(['loadContentType'])
            ->getMock();

        $this->content = new Content(
            [
                'versionInfo' => new VersionInfo(
                    ['contentInfo' => new ContentInfo(['contentTypeId' => 2])]
                ),
            ]
        );

        $this->provider = $this->getMockBuilder(RemoteMediaProvider::class)
            ->disableOriginalConstructor()
            ->setMethods(['buildVariation'])
            ->getMockForAbstractClass();

        $this->requestStack = $this->getMockBuilder(RequestStack::class)
            ->disableOriginalConstructor()
            ->setMethods(['getCurrentRequest'])
            ->getMock();

        $this->logger = $this->getMockBuilder(NullLogger::class)
            ->disableOriginalConstructor()
            ->setMethods([])
            ->getMock();

        $this->remoteMediaHandler = new RemoteMediaHandler(
            $this->fieldHelper,
            $this->translationHelper,
            $this->provider,
            $this->contentTypeService,
            $this->requestStack,
            $this->logger
        );
        $this->remoteMediaHandler->setContent($this->content);

        $this->field = new Field(
            [
                'value' => new Value(
                    ['secure_url' => 'https://res.example.com/some/uri']
                ),
            ]
        );
    }

    public function testInstanceOfHandlerInterface()
    {
        self::assertInstanceOf(HandlerInterface::class, $this->remoteMediaHandler);
    }

    /**
     * @expectedException \eZ\Publish\Core\Base\Exceptions\InvalidArgumentException
     * @expectedExceptionMessage Argument '$params[0]' is invalid: Field type handlers require at least a field identifier.
     */
    public function testGettingTagsWithoutFieldIdentifier()
    {
        $this->remoteMediaHandler->getMetaTags('some_tag', []);
    }

    /**
     * @expectedException \eZ\Publish\Core\Base\Exceptions\InvalidArgumentException
     * @expectedExceptionMessage Argument '$params[0]' is invalid: Field 'some_value' does not exist in content.
     */
    public function testGettingTagsWithNonExistentField()
    {
        $this->translationHelper->expects(self::once())
            ->method('getTranslatedField')
            ->willReturn(null);

        $this->remoteMediaHandler->getMetaTags('some_tag', ['some_value']);
    }

    /**
     * @expectedException \eZ\Publish\Core\Base\Exceptions\InvalidArgumentException
     * @expectedExceptionMessage Argument '$params[0]' is invalid: Netgen\Bundle\RemoteMediaBundle\OpenGraph\Handler\RemoteMediaHandler field type handler does not support field with identifier ''.
     */
    public function testGettingTagsWithUnsupportedField()
    {
        $this->translationHelper->expects(self::once())
            ->method('getTranslatedField')
            ->willReturn(new Field());

        $this->remoteMediaHandler->getMetaTags('some_tag', ['some_value']);
    }

    public function testGettingTagsWithEmptyField()
    {
        $this->translationHelper->expects(self::once())
            ->method('getTranslatedField')
            ->willReturn($this->field);

        $this->fieldHelper->expects(self::once())
            ->method('isFieldEmpty')
            ->willReturn(true);

        $this->remoteMediaHandler->getMetaTags('some_tag', ['some_value']);
    }

    public function testGettingTags()
    {
        $this->contentTypeService->expects(self::once())
            ->method('loadContentType')
            ->with(2)
            ->willReturn(
                new ContentType(
                    [
                        'id' => 2,
                        'identifier' => 'test',
                        'fieldDefinitions' => [],
                    ]
                )
            );

        $this->translationHelper->expects(self::exactly(2))
            ->method('getTranslatedField')
            ->willReturn($this->field);

        $this->fieldHelper->expects(self::once())
            ->method('isFieldEmpty')
            ->willReturn(false);

        $variation = new Variation(['url' => 'https://res.example.com/some/url']);

        $this->provider->expects(self::once())
            ->method('buildVariation')
            ->willReturn($variation);

        $item = $this->remoteMediaHandler->getMetaTags('some_tag', ['some_identifier', 'some_format']);

        self::assertEquals(
            'https://res.example.com/some/url',
            $item[0]->getTagValue()
        );
    }

    public function testGettingTagsWithoutVariationName()
    {
        $this->translationHelper->expects(self::exactly(2))
            ->method('getTranslatedField')
            ->willReturn($this->field);

        $this->fieldHelper->expects(self::once())
            ->method('isFieldEmpty')
            ->willReturn(false);

        $this->provider->expects(self::never())
            ->method('buildVariation');

        /** @var \Netgen\Bundle\OpenGraphBundle\MetaTag\Item[] */
        $item = $this->remoteMediaHandler->getMetaTags('some_tag', ['some_identifier']);

        self::assertEquals(
            'https://res.example.com/some/uri',
            $item[0]->getTagValue()
        );
    }

    public function testGettingTagsWithMultipleArgumentsInArray()
    {
        $this->translationHelper->expects(self::exactly(2))
            ->method('getTranslatedField')
            ->willReturn($this->field);

        $this->requestStack->expects(self::once())
            ->method('getCurrentRequest')
            ->willReturn(new Request());

        $this->remoteMediaHandler->getMetaTags('some_tag', ['some_value', 'some_value_2', '/fallback_path']);
    }
}
