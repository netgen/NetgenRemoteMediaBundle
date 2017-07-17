<?php

namespace Netgen\Bundle\RemoteMediaBundle\Test\MenuPlugin;

use Netgen\Bundle\RemoteMediaBundle\MenuPlugin\RemoteMediaMenuPlugin;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpFoundation\Request;

class RemoteMediaMenuPluginTest extends TestCase
{
    /**
     * @var \Netgen\Bundle\RemoteMediaBundle\MenuPlugin\RemoteMediaMenuPlugin
     */
    protected $plugin;

    public function setUp()
    {
        $this->plugin = new RemoteMediaMenuPlugin();
        parent::setUp();
    }

    public function testIdentifier()
    {
        $this->assertEquals('ngremotemedia', $this->plugin->getIdentifier());
    }

    public function testTemplates()
    {
        $this->assertEquals(
            array(
                'head' => 'NetgenRemoteMediaBundle:ngadminui/plugin:head.html.twig',
                'aside' => '@NetgenAdminUI/menu/plugins/legacy/aside.html.twig',
                'left' => '@NetgenAdminUI/menu/plugins/legacy/left.html.twig',
                'top' => '@NetgenAdminUI/menu/plugins/legacy/top.html.twig',
            ),
            $this->plugin->getTemplates()
        );
    }

    public function testIsActive()
    {
        $this->assertEquals(true, $this->plugin->isActive());
    }

    public function testMatches()
    {
        $fakeRequest = Request::create('/', 'GET');

        $this->assertEquals(true, $this->plugin->matches($fakeRequest));
    }
}
