<?php

namespace Netgen\Bundle\RemoteMediaBundle\Tests\Templating\Twig\Extension;

use eZ\Publish\Core\Helper\TranslationHelper;
use eZ\Publish\API\Repository\ContentTypeService;
use Netgen\Bundle\RemoteMediaBundle\RemoteMedia\Helper;
use Netgen\Bundle\RemoteMediaBundle\RemoteMedia\RemoteMediaProvider;
use Netgen\Bundle\RemoteMediaBundle\Templating\Twig\Extension\NetgenRemoteMediaExtension;
use PHPUnit\Framework\TestCase;

class NetgenRemoteMediaExtensionTest extends TestCase
{
    /**
     * @var \Netgen\Bundle\RemoteMediaBundle\Templating\Twig\Extension\NetgenRemoteMediaExtension
     */
    protected $extension;

    protected $provider;
    protected $translationHelper;
    protected $contentTypeService;
    protected $helper;

    public function setUp()
    {
        $this->provider = $this->createMock(RemoteMediaProvider::class);
        $this->translationHelper = $this->createMock(TranslationHelper::class);
        $this->contentTypeService = $this->createMock(ContentTypeService::class);
        $this->helper = $this->createMock(Helper::class);

        $this->extension = new NetgenRemoteMediaExtension(
            $this->provider,
            $this->translationHelper,
            $this->contentTypeService,
            $this->helper
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
}
