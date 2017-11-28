<?php

namespace Netgen\Bundle\RemoteMediaBundle\Tests\OpenGraph\Handler\RemoteMediaHandler;

use eZ\Publish\API\Repository\Values\Content\ContentInfo;
use eZ\Publish\Core\Repository\ContentTypeService;
use Netgen\Bundle\OpenGraphBundle\Tests\Handler\FieldType\HandlerBaseTest;
use Netgen\Bundle\RemoteMediaBundle\Core\FieldType\RemoteMedia\Variation;
use Netgen\Bundle\RemoteMediaBundle\OpenGraph\Handler\RemoteMediaHandler;
use Netgen\Bundle\RemoteMediaBundle\RemoteMedia\RemoteMediaProvider;
use eZ\Publish\API\Repository\Values\Content\Field;
use Netgen\Bundle\RemoteMediaBundle\Core\FieldType\RemoteMedia\Value;
use eZ\Publish\Core\Helper\FieldHelper;
use eZ\Publish\Core\Helper\TranslationHelper;
use eZ\Publish\Core\Repository\Values\Content\Content;
use eZ\Publish\Core\Repository\Values\Content\VersionInfo;
use Netgen\Bundle\OpenGraphBundle\Handler\HandlerInterface;
use Psr\Log\NullLogger;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestStack;
use eZ\Publish\Core\Repository\Values\ContentType\ContentType;

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

    public function setUp()
    {
        $this->fieldHelper = $this->getMockBuilder(FieldHelper::class)
            ->disableOriginalConstructor()
            ->setMethods(array('isFieldEmpty'))
            ->getMock();

        $this->translationHelper = $this->getMockBuilder(TranslationHelper::class)
            ->disableOriginalConstructor()
            ->setMethods(array('getTranslatedField'))
            ->getMock();

        $this->contentTypeService = $this->getMockBuilder(ContentTypeService::class)
            ->disableOriginalConstructor()
            ->setMethods(array('loadContentType'))
            ->getMock();

        $this->content = new Content(
            array(
                'versionInfo' => new VersionInfo(
                    array('contentInfo' => new ContentInfo(array('contentTypeId' => 2)))
                )
            )
        );

        $this->provider = $this->getMockBuilder(RemoteMediaProvider::class)
            ->disableOriginalConstructor()
            ->setMethods(array('buildVariation'))
            ->getMockForAbstractClass();

        $this->requestStack = $this->getMockBuilder(RequestStack::class)
            ->disableOriginalConstructor()
            ->setMethods(array('getCurrentRequest'))
            ->getMock();

        $this->logger = $this->getMockBuilder(NullLogger::class)
            ->disableOriginalConstructor()
            ->setMethods(array())
            ->getMock();

        $this->remoteMediaHandler = new RemoteMediaHandler(
            $this->fieldHelper, $this->translationHelper, $this->provider, $this->contentTypeService, $this->requestStack, $this->logger
        );
        $this->remoteMediaHandler->setContent($this->content);

        $this->field = new Field(
            array(
                'value' => new Value(
                    array('secure_url' => 'https://res.example.com/some/uri')
                )
            )
        );
    }

    public function testInstanceOfHandlerInterface()
    {
        $this->assertInstanceOf(HandlerInterface::class, $this->remoteMediaHandler);
    }

    /**
     * @expectedException \eZ\Publish\Core\Base\Exceptions\InvalidArgumentException
     * @expectedExceptionMessage Argument '$params[0]' is invalid: Field type handlers require at least a field identifier.
     */
    public function testGettingTagsWithoutFieldIdentifier()
    {
        $this->remoteMediaHandler->getMetaTags('some_tag', array());
    }

    /**
     * @expectedException \eZ\Publish\Core\Base\Exceptions\InvalidArgumentException
     * @expectedExceptionMessage Argument '$params[0]' is invalid: Field 'some_value' does not exist in content.
     */
    public function testGettingTagsWithNonExistentField()
    {
        $this->translationHelper->expects($this->once())
            ->method('getTranslatedField')
            ->willReturn(null);

        $this->remoteMediaHandler->getMetaTags('some_tag', array('some_value'));
    }

    /**
     * @expectedException \eZ\Publish\Core\Base\Exceptions\InvalidArgumentException
     * @expectedExceptionMessage Argument '$params[0]' is invalid: Netgen\Bundle\RemoteMediaBundle\OpenGraph\Handler\RemoteMediaHandler field type handler does not support field with identifier ''.
     */
    public function testGettingTagsWithUnsupportedField()
    {
        $this->translationHelper->expects($this->once())
            ->method('getTranslatedField')
            ->willReturn(new Field());

        $this->remoteMediaHandler->getMetaTags('some_tag', array('some_value'));
    }

    public function testGettingTagsWithEmptyField()
    {
        $this->translationHelper->expects($this->once())
            ->method('getTranslatedField')
            ->willReturn($this->field);

        $this->fieldHelper->expects($this->once())
            ->method('isFieldEmpty')
            ->willReturn(true);

        $this->remoteMediaHandler->getMetaTags('some_tag', array('some_value'));
    }

    public function testGettingTags()
    {
        $this->contentTypeService->expects($this->once())
            ->method('loadContentType')
            ->with(2)
            ->willReturn(
                new ContentType(
                    array(
                        'id' => 2,
                        'identifier' => 'test',
                        'fieldDefinitions' => array()
                    )
                )
            );

        $this->translationHelper->expects($this->exactly(2))
            ->method('getTranslatedField')
            ->willReturn($this->field);

        $this->fieldHelper->expects($this->once())
            ->method('isFieldEmpty')
            ->willReturn(false);


        $variation = new Variation(array('url' => 'https://res.example.com/some/url'));

        $this->provider->expects($this->once())
            ->method('buildVariation')
            ->willReturn($variation);

        $item = $this->remoteMediaHandler->getMetaTags('some_tag', array('some_identifier', 'some_format'));

        $this->assertEquals(
            'https://res.example.com/some/url',
            $item[0]->getTagValue()
        );
    }

    public function testGettingTagsWithoutVariationName()
    {
        $this->translationHelper->expects($this->exactly(2))
            ->method('getTranslatedField')
            ->willReturn($this->field);

        $this->fieldHelper->expects($this->once())
            ->method('isFieldEmpty')
            ->willReturn(false);

        $this->provider->expects($this->never())
            ->method('buildVariation');

        /** @var \Netgen\Bundle\OpenGraphBundle\MetaTag\Item[] */
        $item = $this->remoteMediaHandler->getMetaTags('some_tag', array('some_identifier'));

        $this->assertEquals(
            'https://res.example.com/some/uri',
            $item[0]->getTagValue()
        );
    }

    public function testGettingTagsWithMultipleArgumentsInArray()
    {
        $this->translationHelper->expects($this->exactly(2))
            ->method('getTranslatedField')
            ->willReturn($this->field);

        $this->remoteMediaHandler->getMetaTags('some_tag', array('some_value', 'some_value_2'));
    }
}
