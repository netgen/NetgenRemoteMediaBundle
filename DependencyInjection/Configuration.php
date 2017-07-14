<?php

namespace Netgen\Bundle\RemoteMediaBundle\DependencyInjection;

use eZ\Bundle\EzPublishCoreBundle\DependencyInjection\Configuration\SiteAccessAware\Configuration as SiteAccessConfiguration;
use Symfony\Component\Config\Definition\Builder\ArrayNodeDefinition;
use Symfony\Component\Config\Definition\Builder\NodeBuilder;
use Symfony\Component\Config\Definition\Builder\TreeBuilder;

class Configuration extends SiteAccessConfiguration
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

        $systemNode = $this->generateScopeBaseNode($rootNode);
        $this->addImageConfiguration($systemNode);

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
                    ->cannotBeEmpty()
                    ->defaultNull()
                ->end()
                    ->scalarNode('account_key')
                    ->isRequired()
                    ->cannotBeEmpty()
                    ->defaultNull()
                ->end()
                ->scalarNode('account_secret')
                    ->isRequired()
                    ->defaultNull()
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
                    ->end()
                ->end()
            ->end()
        ;
    }

    protected function addImageConfiguration(NodeBuilder $nodeBuilder)
    {
        $nodeBuilder
            ->arrayNode('image_variations')
                ->info('Configuration for your image variations (aka "image aliases")')
                ->example(
                    array(
                        'variation_name' => array(
                            'transformations' => array(
                                array(
                                    'name' => 'resize',
                                    'params' => array(400, 350),
                                ),
                            ),
                        ),
                        'my_cropped_variation' => array(
                            'transformations' => array(
                                array(
                                    'name' => 'fill',
                                    'params' => array(300, 200),
                                ),
                                array(
                                    'name' => 'crop',
                                    'params' => array(300, 300, 0, 0),
                                ),
                            ),
                        ),
                    )
                )
                ->useAttributeAsKey('variation_name')
                ->normalizeKeys(false)
                ->prototype('array')
                    ->useAttributeAsKey('content_type_identifier')
                    ->normalizeKeys(false)
                    ->prototype('array')
                        ->children()
                            ->arrayNode('transformations')
                                ->info('A list of transformations to apply to the image')
                                ->useAttributeAsKey('name')
                                ->normalizeKeys(false)
                                ->prototype('array')
                                ->info('Array/Hash of parameters to pass to the filter')
                                    ->useAttributeAsKey('options')
                                    ->beforeNormalization()
                                        ->ifTrue(
                                            function ($v) {
                                                // Check if passed array only contains a "params" key (BC with <=5.3).
                                                return is_array($v) && count($v) === 1 && isset($v['params']);
                                            }
                                        )
                                        ->then(
                                            function ($v) {
                                                // If we have the "params" key, just use the value.
                                                return $v['params'];
                                            }
                                        )
                                    ->end()
                                    ->prototype('variable')->end()
                                ->end()
                            ->end()
                        ->end()
                    ->end()
                ->end()
            ->end();
    }
}
