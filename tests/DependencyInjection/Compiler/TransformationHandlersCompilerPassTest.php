<?php

namespace Netgen\Bundle\RemoteMediaBundle\Tests\DependencyInjection\Compiler;

use Matthias\SymfonyDependencyInjectionTest\PhpUnit\AbstractCompilerPassTestCase;
use Netgen\Bundle\RemoteMediaBundle\DependencyInjection\Compiler\TransformationHandlersCompilerPass;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Definition;
use Symfony\Component\DependencyInjection\Reference;

class TransformationHandlersCompilerPassTest extends AbstractCompilerPassTestCase
{
    public function testCompilerPassCollectsValidServices()
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
            ]
        );
    }

    /**
     * @expectedException \LogicException
     * @expectedExceptionMessage netgen_remote_media.transformation_handler service tag needs an "alias" attribute to identify the handler. None given.
     */
    public function testCompilerPassFailsMissingAlias()
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

        $this->compile();
    }

    /**
     * @expectedException \LogicException
     * @expectedExceptionMessage netgen_remote_media.transformation_handler service tag needs an "provider" attribute to identify which providers it supports. None given.
     */
    public function testCompilerPassFailsMissingProvider()
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

        $this->compile();
    }

    protected function registerCompilerPass(ContainerBuilder $container)
    {
        $container->addCompilerPass(new TransformationHandlersCompilerPass());
    }
}
