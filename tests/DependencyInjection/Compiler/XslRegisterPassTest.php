<?php

namespace Netgen\Bundle\RemoteMediaBundle\Tests\DependencyInjection\Compiler;

use Matthias\SymfonyDependencyInjectionTest\PhpUnit\AbstractCompilerPassTestCase;
use Netgen\Bundle\RemoteMediaBundle\DependencyInjection\Compiler\XslRegisterPass;
use Symfony\Component\DependencyInjection\ContainerBuilder;

class XslRegisterPassTest extends AbstractCompilerPassTestCase
{
    public function testCompilerPassWithValidParameters()
    {
        $siteaccessList = ['siteacc1', 'siteacc2'];

        $testXslConfigDefault = [
            [
                'path' => 'test/test1.xsl',
                'priority' => 0,
            ],
        ];
        $testXslConfig = [
            [
                'path' => 'test/test.xsl',
                'priority' => 5000,
            ],
        ];

        $expectedInjectedParameter = [
            'path' => str_replace('tests/', 'bundle/', __DIR__).'/../../Resources/xsl/ezxml_ngremotemedia.xsl',
            'priority' => 5000,
        ];

        $expectedXslConfig = array_merge($testXslConfig, [$expectedInjectedParameter]);

        $this->setParameter('ezsettings.default.fieldtypes.ezxml.custom_xsl', $testXslConfigDefault);
        $this->setParameter('ezpublish.siteaccess.list', $siteaccessList);

        foreach ($siteaccessList as $siteaccess) {
            $this->setParameter('ezsettings.'.$siteaccess.'.fieldtypes.ezxml.custom_xsl', $testXslConfig);
        }

        $this->compile();

        foreach ($siteaccessList as $siteaccess) {
            $this->assertContainerBuilderHasParameter('ezsettings.'.$siteaccess.'.fieldtypes.ezxml.custom_xsl', $expectedXslConfig);
        }
    }

    public function testCompilerPassWithoutExistingXslConfig()
    {
        $siteaccessList = ['siteacc1', 'siteacc2'];

        $this->setParameter('ezpublish.siteaccess.list', $siteaccessList);

        $this->compile();
    }

    protected function registerCompilerPass(ContainerBuilder $container)
    {
        $container->addCompilerPass(new XslRegisterPass());
    }
}
