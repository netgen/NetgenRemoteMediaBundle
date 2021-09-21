<?php

declare(strict_types=1);

namespace Netgen\Bundle\RemoteMediaBundle\Tests\DependencyInjection\Compiler;

use Matthias\SymfonyDependencyInjectionTest\PhpUnit\AbstractCompilerPassTestCase;
use Netgen\Bundle\RemoteMediaBundle\DependencyInjection\CompilerPass\CachePoolPass;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Definition;

final class CachePoolPassTest extends AbstractCompilerPassTestCase
{
    /**
     * @covers \Netgen\Bundle\RemoteMediaBundle\DependencyInjection\CompilerPass\CachePoolPass::process
     */
    public function testCompilerPass(): void
    {
        $this->setDefinition('cache.app', new Definition());
        $this->setParameter('netgen_remote_media.cache.pool_name', 'cache.app');

        $this->compile();

        $this->assertContainerBuilderHasService(
            'netgen_remote_media.cache.pool',
        );
    }

    /**
     * @covers \Netgen\Bundle\RemoteMediaBundle\DependencyInjection\CompilerPass\CachePoolPass::process
     */
    public function testCompilerPassWithoutParameter(): void
    {
        $this->compile();

        $this->assertContainerBuilderNotHasService(
            'netgen_remote_media.cache.pool',
        );
    }

    protected function registerCompilerPass(ContainerBuilder $container): void
    {
        $container->addCompilerPass(new CachePoolPass());
    }
}
