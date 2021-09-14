<?php

declare(strict_types=1);

namespace Netgen\Bundle\RemoteMediaBundle\Tests\DependencyInjection\Compiler;

use LogicException;
use Matthias\SymfonyDependencyInjectionTest\PhpUnit\AbstractCompilerPassTestCase;
use Netgen\Bundle\RemoteMediaBundle\DependencyInjection\CompilerPass\TransformationHandlersPass;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Definition;
use Symfony\Component\DependencyInjection\Reference;

final class TransformationHandlersCompilerPassTest extends AbstractCompilerPassTestCase
{
    /**
     * @covers \Netgen\Bundle\RemoteMediaBundle\DependencyInjection\CompilerPass\TransformationHandlersPass::process
     */
    public function testCompilerPassCollectsValidServices(): void
    {
        $registry = new Definition();

        $this->setDefinition('netgen_remote_media.handler_registry', $registry);

        $transformationHandler = new Definition();

        $tags = [
            'netgen_remote_media.transformation_handler' => [
                [
                    'alias' => 'testalias',
                    'provider' => 'testprovider',
                ],
            ],
        ];

        $transformationHandler->setTags($tags);
        $this->setDefinition('custom_handler', $transformationHandler);

        $this->compile();

        $this->assertContainerBuilderHasServiceDefinitionWithMethodCall(
            'netgen_remote_media.handler_registry',
            'addHandler',
            [
                'testprovider',
                'testalias',
                new Reference('custom_handler'),
            ],
        );
    }

    /**
     * @covers \Netgen\Bundle\RemoteMediaBundle\DependencyInjection\CompilerPass\TransformationHandlersPass::process
     */
    public function testCompilerPassFailsMissingAlias(): void
    {
        $registry = new Definition();

        $this->setDefinition('netgen_remote_media.handler_registry', $registry);

        $transformationHandler = new Definition();

        $tags = [
            'netgen_remote_media.transformation_handler' => [
                [
                    'provider' => 'testprovider',
                ],
            ],
        ];

        $transformationHandler->setTags($tags);
        $this->setDefinition('custom_handler', $transformationHandler);

        $this->expectException(LogicException::class);
        $this->expectExceptionMessage('netgen_remote_media.transformation_handler service tag needs an "alias" attribute to identify the handler. None given.');

        $this->compile();
    }

    /**
     * @covers \Netgen\Bundle\RemoteMediaBundle\DependencyInjection\CompilerPass\TransformationHandlersPass::process
     */
    public function testCompilerPassFailsMissingProvider(): void
    {
        $registry = new Definition();

        $this->setDefinition('netgen_remote_media.handler_registry', $registry);

        $transformationHandler = new Definition();

        $tags = [
            'netgen_remote_media.transformation_handler' => [
                [
                    'alias' => 'testalias',
                ],
            ],
        ];

        $transformationHandler->setTags($tags);
        $this->setDefinition('custom_handler', $transformationHandler);

        $this->expectException(LogicException::class);
        $this->expectExceptionMessage('netgen_remote_media.transformation_handler service tag needs an "provider" attribute to identify which providers it supports. None given.');

        $this->compile();
    }

    protected function registerCompilerPass(ContainerBuilder $container): void
    {
        $container->addCompilerPass(new TransformationHandlersPass());
    }
}
