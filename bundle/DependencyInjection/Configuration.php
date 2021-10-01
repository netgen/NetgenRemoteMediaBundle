<?php

declare(strict_types=1);

namespace Netgen\Bundle\RemoteMediaBundle\DependencyInjection;

use Symfony\Component\Config\Definition\Builder\ArrayNodeDefinition;
use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use Symfony\Component\Config\Definition\ConfigurationInterface;
use function count;
use function is_array;

final class Configuration implements ConfigurationInterface
{
    public function getConfigTreeBuilder(): TreeBuilder
    {
        $treeBuilder = new TreeBuilder('netgen_remote_media');
        $this->addProviderSection($treeBuilder->getRootNode());
        $this->addImageConfiguration($treeBuilder->getRootNode());
        $this->addCacheConfiguration($treeBuilder->getRootNode());

        return $treeBuilder;
    }

    private function addProviderSection(ArrayNodeDefinition $rootNode): void
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
            ->end();
    }

    private function addImageConfiguration(ArrayNodeDefinition $rootNode): void
    {
        $rootNode
            ->children()
                ->arrayNode('image_variations')
                    ->info('Configuration for your image variations (aka "image aliases")')
                    ->example(
                        [
                            'variation_name' => [
                                'transformations' => [
                                    [
                                        'name' => 'resize',
                                        'params' => [400, 350],
                                    ],
                                ],
                            ],
                            'my_cropped_variation' => [
                                'transformations' => [
                                    [
                                        'name' => 'fill',
                                        'params' => [300, 200],
                                    ],
                                    [
                                        'name' => 'crop',
                                        'params' => [300, 300, 0, 0],
                                    ],
                                ],
                            ],
                        ],
                    )
                    ->useAttributeAsKey('variation_name')
                    ->normalizeKeys(false)
                    ->prototype('array')
                        ->useAttributeAsKey('group')
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
                                                static function ($v) {
                                                    // Check if passed array only contains a "params" key
                                                    return is_array($v) && count($v) === 1 && isset($v['params']);
                                                },
                                            )
                                            ->then(
                                                static function ($v) {
                                                    // If we have the "params" key, just use the value.
                                                    return $v['params'];
                                                },
                                            )
                                        ->end()
                                        ->prototype('variable')->end()
                                    ->end()
                                ->end()
                            ->end()
                        ->end()
                    ->end()
                ->end()
            ->end();
    }

    private function addCacheConfiguration(ArrayNodeDefinition $rootNode): void
    {
        $rootNode
            ->children()
                ->arrayNode('cache')
                    ->children()
                        ->scalarNode('pool')
                        ->end()
                        ->integerNode('ttl')
                        ->end()
                    ->end()
                ->end()
            ->end();
    }
}
