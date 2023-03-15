<?php

declare(strict_types=1);

namespace Netgen\Bundle\RemoteMediaBundle\Tests\DependencyInjection\Compiler;

use Matthias\SymfonyDependencyInjectionTest\PhpUnit\AbstractCompilerPassTestCase;
use Netgen\Bundle\RemoteMediaBundle\DependencyInjection\CompilerPass\CachePoolPass;
use PHPUnit\Framework\Attributes\CoversClass;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Definition;

#[CoversClass(CachePoolPass::class)]
final class CachePoolPassTest extends AbstractCompilerPassTestCase
{
    public function testCompilerPass(): void
    {
        $this->setDefinition('cache.app', new Definition());
        $this->setParameter('netgen_remote_media.cache.pool_name', 'cache.app');

        $this->compile();

        $this->assertContainerBuilderHasService(
            'netgen_remote_media.cache.pool',
        );
    }

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
