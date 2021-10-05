<?php

declare(strict_types=1);

namespace Netgen\Bundle\RemoteMediaBundle\Tests\Templating\Twig\Extension;

use Netgen\Bundle\RemoteMediaBundle\Templating\Twig\Extension\RemoteMediaExtension;
use PHPUnit\Framework\TestCase;
use Twig\TwigFilter;
use Twig\TwigFunction;

final class RemoteMediaExtensionTest extends TestCase
{
    private RemoteMediaExtension $extension;

    protected function setUp(): void
    {
        $this->extension = new RemoteMediaExtension();
    }

    /**
     * @covers \Netgen\Bundle\RemoteMediaBundle\Templating\Twig\Extension\RemoteMediaExtension::getFunctions
     */
    public function testGetFunctions(): void
    {
        self::assertNotEmpty($this->extension->getFunctions());
        self::assertContainsOnlyInstancesOf(TwigFunction::class, $this->extension->getFunctions());
    }

    /**
     * @covers \Netgen\Bundle\RemoteMediaBundle\Templating\Twig\Extension\RemoteMediaExtension::getFilters
     */
    public function testGetFilters(): void
    {
        self::assertNotEmpty($this->extension->getFilters());
        self::assertContainsOnlyInstancesOf(TwigFilter::class, $this->extension->getFilters());
    }
}
