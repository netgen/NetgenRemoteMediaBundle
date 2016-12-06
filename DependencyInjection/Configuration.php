<?php

namespace Netgen\Bundle\RemoteMediaBundle\DependencyInjection;

use Symfony\Component\Config\Definition\Builder\ArrayNodeDefinition;
use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use Symfony\Component\Config\Definition\ConfigurationInterface;

class Configuration implements ConfigurationInterface
{
    /**
     * {@inheritdoc}
     */
    public function getConfigTreeBuilder()
    {
        $treeBuilder = new TreeBuilder();
        $rootNode = $treeBuilder->root('netgen_remote_media');

        $this->addProviderSection($rootNode);
        $this->addEzoeSection($rootNode);

        return $treeBuilder;
    }

    protected function addProviderSection(ArrayNodeDefinition $rootNode)
    {
        $rootNode
            ->children()
                ->scalarNode('provider')
                    ->defaultValue('cloudinary')
                ->end()
                ->scalarNode('account_name')
                    ->isRequired()
                    ->defaultNull()
                ->end()
                    ->scalarNode('account_key')
                    ->isRequired()
                    ->defaultNull()
                ->end()
                ->scalarNode('account_secret')
                    ->isRequired()
                    ->defaultNull()
                ->end()
                    ->integerNode('browse_limit')
                    ->defaultValue(500)
                ->end()
                ->booleanNode('remove_unused')
                    ->defaultValue(false)
                ->end()
            ->end()
        ;
    }

    protected function addEzoeSection(ArrayNodeDefinition $rootNode)
    {
        $rootNode
            ->children()
                ->arrayNode('ezoe')
                    ->addDefaultsIfNotSet()
                    ->treatNullLike(array())
                    ->children()
                        ->variableNode('class_list')
                            ->defaultValue(array(
                                'pull-left|Left adjusted',
                                'pull-right|Right adjusted'
                            ))
                        ->end()
                        ->variableNode('variation_list')
                            ->defaultValue(array(
                                'Small,200x200',
                                'Medium,400x400',
                                'Large,800x600'
                            ))
                        ->end()
                    ->end()
                ->end()
            ->end()
        ;
    }
}
