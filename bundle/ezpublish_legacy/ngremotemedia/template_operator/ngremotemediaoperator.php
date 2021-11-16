<?php

use Netgen\Bundle\RemoteMediaBundle\Core\FieldType\RemoteMedia\Variation;
use Netgen\Bundle\RemoteMediaBundle\Core\FieldType\RemoteMedia\Value;
use eZ\Publish\API\Repository\Exceptions\NotFoundException;

class NgRemoteMediaOperator
{

    /**
     * @return array
     */
    function operatorList()
    {
        return array(
            'ngremotemedia',
            'ng_remote_resource',
            'ng_remote_croppable',
            'ngremotevideo',
            'videoThumbnail',
            'ng_image_variations',
            'scaling_format',
            'list_format',
        );
    }

    /**
     * @return bool
     */
    function namedParameterPerOperator()
    {
        return true;
    }

    /**
     * @return array
     */
    function namedParameterList()
    {
        return array(
            'ng_image_variations' => array(
                'class_identifier' => array(
                    'type' => 'string',
                    'required' => true
                ),
                'only_croppable' => array(
                    'type' => 'boolean',
                    'required' => false,
                    'default' => false
                )
            ),
            'ngremotemedia' => array(
                'value' => array(
                    'type' => 'Value',
                    'required' => true
                ),
                'content_type_identifier' => array(
                    'type' => 'string',
                    'required' => true
                ),
                'format' => array(
                    'type' => 'string',
                    'required' => true
                ),
                'secure' => array(
                    'type' => 'boolean',
                    'required' => false,
                    'default' => true
                )
            ),
            'ng_remote_resource' => array(
                'resource_type' => array(
                    'type' => 'string',
                    'required' => true
                ),
                'resource_id' => array(
                    'type' => 'string',
                    'required' => false
                ),
                'image_variations' => array(
                    'type' => 'string',
                    'required' => false
                )
            ),
            'ng_remote_croppable' => array(
                'class_identifier' => array(
                    'type' => 'string',
                    'required' => true
                )
            ),
            'scaling_format' => array(
                'formats' => array(
                    'type' => 'array',
                    'required' => true
                )
            ),
            'list_format' => array(
                'formats' => array(
                    'type' => 'array',
                    'required' => true
                )
            ),
            'videoThumbnail' => array(
                'value' => array(
                    'type' => 'Value',
                    'required' => true
                ),
                'options' => array(
                    'type' => 'array',
                    'required' => false
                )
            ),
            'ngremotevideo' => array(
                'value' => array(
                    'type' => 'Value',
                    'required' => true
                ),
                'availableFormats' => array(
                    'type' => 'array',
                    'required' => true
                ),
                'format' => array(
                    'type' => 'string',
                    'required' => false,
                    'default' => ''
                )
            ),
        );
    }

    function modify(
        $tpl,
        $operatorName,
        $operatorParameters,
        $rootNamespace,
        $currentNamespace,
        &$operatorValue,
        $namedParameters,
        $placement
    ) {
        if ($operatorName === 'ngremotemedia') {
            $operatorValue = $this->ngremotemedia(
                $namedParameters['value'],
                $namedParameters['content_type_identifier'],
                $namedParameters['format'],
                $namedParameters['secure']
            );
        } elseif ($operatorName === 'ng_remote_resource') {
            $operatorValue = $this->getRemoteResource($namedParameters['resource_type'], $namedParameters['resource_id'], $namedParameters['image_variations']);
        } elseif ($operatorName === 'ng_remote_croppable') {
            $operatorValue = $this->isCroppable($namedParameters['class_identifier']);
        } elseif ($operatorName === 'videoThumbnail') {
            $operatorValue = $this->videoThumbnail($namedParameters['value'], $namedParameters['options']);
        } elseif ($operatorName === 'ngremotevideo') {
            $operatorValue = $this->getvideoTag(
                $namedParameters['value'],
                $namedParameters['availableFormats'],
                $namedParameters['format']
            );
        } elseif ($operatorName === 'ng_image_variations') {
            $onlyCroppable = $namedParameters['only_croppable'] ?: false;
            $operatorValue = $this->getImageVariations($namedParameters['class_identifier'], $onlyCroppable);
        } elseif ($operatorName === 'scaling_format') {
            $operatorValue = $this->formatAliasForScaling($namedParameters['formats']);
        } elseif ($operatorName === 'list_format') {
            $operatorValue = $this->formatAliasForList($namedParameters['formats']);
        }
    }

    function formatAliasForScaling($variations)
    {
        if (empty($variations)) {
            return $variations;
        }

        $availableVariations = array();

        foreach ($variations as $variationName => $variationConfig) {
            foreach($variationConfig['transformations'] as $name => $config) {
                if ($name !== 'crop') {
                    continue;
                }

                $availableVariations[$variationName] = $config;
            }
        }

        return $availableVariations;
    }

    function formatAliasForList($variations)
    {
        return array_keys($variations);
    }

    function getImageVariations($class_identifier, $onlyCroppable = false)
    {
        $container = ezpKernel::instance()->getServiceContainer();
        $variationResolver = $container->get('netgen_remote_media.variation.resolver');

        if (!$onlyCroppable) {
            return $variationResolver->getVariationsForContentType($class_identifier);
        } else {
            return $variationResolver->getCroppbableVariations($class_identifier);
        }
    }

    function getRemoteResource($resource_type, $resource_id, $image_variations = null)
    {
        $container = ezpKernel::instance()->getServiceContainer();
        $provider = $container->get('netgen_remote_media.provider');

        $value = $provider->getRemoteResource($resource_id, $resource_type);
        $value->variations = json_decode($image_variations, true);

        return $value;
    }

    function ngremotemedia($value, $content_type_identifier, $format, $secure = true)
    {
        $container = ezpKernel::instance()->getServiceContainer();
        $provider = $container->get( 'netgen_remote_media.provider' );

        return $provider->buildVariation($value, $content_type_identifier, $format, $secure);
    }

    function isCroppable($class_identifier)
    {
        $container = ezpKernel::instance()->getServiceContainer();
        $variationResolver = $container->get('netgen_remote_media.variation.resolver');

        return !empty($variationResolver->getCroppbableVariations($class_identifier));
    }

    function videoThumbnail($value, $options = [])
    {
        $container = ezpKernel::instance()->getServiceContainer();
        $provider = $container->get( 'netgen_remote_media.provider' );

        return $provider->getVideoThumbnail($value, $options);
    }

    function getVideoTag($value, $availableFormats, $format)
    {
        $container = ezpKernel::instance()->getServiceContainer();
        $provider = $container->get( 'netgen_remote_media.provider' );

        return $provider->generateVideoTag($value, $format, $availableFormats);
    }
}
