<?php

use Netgen\Bundle\RemoteMediaBundle\Core\FieldType\RemoteMedia\Variation;
use Netgen\Bundle\RemoteMediaBundle\Core\FieldType\RemoteMedia\Value;

class NgRemoteMediaOperator
{

    /**
     * @return array
     */
    function operatorList()
    {
        return array('ngremotemedia', 'ng_remote_croppable', 'videoThumbnail', 'ng_image_variations');
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
                'only_croppable' => array(
                    'type' => 'boolean',
                    'required' => false
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
            'ng_remote_croppable' => array(
                'value' => array(
                    'type' => 'Value',
                    'required' => true
                )
            ),
            'videoThumbnail' => array(
                'value' => array(
                    'type' => 'Value',
                    'required' => true
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
                    'required' => false
                )
            )
        );
    }

    function modify($tpl, $operatorName, $operatorParameters, $rootNamespace, $currentNamespace, &$operatorValue, $namedParameters, $placement)
    {
        if ($operatorName === 'ngremotemedia') {
            $operatorValue = $this->ngremotemedia(
                $namedParameters['value'],
                $namedParameters['content_type_identifier'],
                $namedParameters['format'],
                $namedParameters['secure']
            );
        } elseif ($operatorName === 'ng_remote_croppable') {
            $onlyCroppable = isset($namedParameters['only_croppable']) ? $namedParameters['only_croppable'] : false;
            $operatorValue = $this->isCroppable($namedParameters['value'], $onlyCroppable);
        } elseif ($operatorName === 'videoThumbnail') {
            $operatorValue = $this->videoThumbnail($namedParameters['value']);
        } elseif ($operatorName === 'ngremotevideo') {
            $operatorValue = $this->getvideoTag(
                $namedParameters['value'],
                $namedParameters['availableFormats'],
                $namedParameters['format']
            );
        } elseif ($operatorName === 'ng_image_variations') {
            $operatorValue = $this->getImageVariations();
        }
    }

    function getImageVariations($onlyCroppable = false)
    {
        $container = ezpKernel::instance()->getServiceContainer();

        return $container->get('ezpublish.config.resolver')->getParameter('image_variations', 'netgen_remote_media');
    }

    function ngremotemedia($value, $content_type_identifier, $format, $secure = true)
    {
        $container = ezpKernel::instance()->getServiceContainer();
        $provider = $container->get( 'netgen_remote_media.provider' );

        return $provider->getVariation($value, $content_type_identifier, $format, $secure);
    }

    function isCroppable($value)
    {
        $valueWidth = $value.metaData.width;
        $valueHeight = $value.metaData.height;

        $aliases = $this->getImageVariations();

        foreach ($aliases as $alias => $configuration) {
            foreach ($configuration['transformations'] as $transformationName => $transformationOptions) {
                if ($transformationName === 'crop') {
                    return true;
                }
            }
        }

        return false;
    }

    function videoThumbnail($value)
    {
        $container = ezpKernel::instance()->getServiceContainer();
        $provider = $container->get( 'netgen_remote_media.provider' );

        return $provider->getVideoThumbnail($value->resourceId);
    }

    function getVideoTag($value, $availableFormats, $format)
    {
        $container = ezpKernel::instance()->getServiceContainer();
        $provider = $container->get( 'netgen_remote_media.provider' );

        return $provider->generateVideoTag($value->resourceId, $format, $availableFormats);
    }
}
