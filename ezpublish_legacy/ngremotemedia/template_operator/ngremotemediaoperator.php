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
        return array('ngremotemedia', 'mediaFits', 'videoThumbnail');
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
            'ngremotemedia' => array(
                'value' => array(
                    'type' => 'Value',
                    'required' => true
                ),
                'format' => array(
                    'type' => 'string',
                    'required' => true
                ),
                'availableFormats' => array(
                    'type' => 'array',
                    'required' => true
                ),
                'secure' => array(
                    'type' => 'boolean',
                    'required' => false,
                    'default' => true
                )
            ),
            'mediaFits' => array(
                'value' => array(
                    'type' => 'Value',
                    'required' => true
                ),
                'variations' => array(
                    'type' => 'array',
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
            if (empty($namedParameters['value'])) {
                $operatorValue = null;

                return;
            }

            $operatorValue = $this->ngremotemedia(
                $namedParameters['value'],
                $namedParameters['format'],
                $namedParameters['availableFormats'],
                $namedParameters['secure']
            );
        } elseif ($operatorName === 'mediaFits') {
            $operatorValue = $this->mediaFits($namedParameters['value'], $namedParameters['variations']);
        } elseif ($operatorName === 'videoThumbnail') {
            $operatorValue = $this->videoThumbnail($namedParameters['value']);
        } elseif ($operatorName === 'ngremotevideo') {
            $operatorValue = $this->getvideoTag(
                $namedParameters['value'],
                $namedParameters['availableFormats'],
                $namedParameters['format']
            );
        }
    }

    function ngremotemedia($value, $format, $availableFormats, $secure = true)
    {
        $container = ezpKernel::instance()->getServiceContainer();
        $provider = $container->get( 'netgen_remote_media.provider' );

        return $provider->getVariation($value, $format, $availableFormats, $secure);
    }

    function mediaFits($value, $variations)
    {
        $valueWidth = $value.metaData.width;
        $valueHeight = $value.metaData.height;

        $variations = json_decode($variations, true);

        foreach($variations as $variationName => $variationSize) {
            $variationSizeArray = explode('x', $variationSize);

            if ($valueWidth < $variationSizeArray[0] || $valueHeight < $variationSizeArray[1]) {
                return false;
            }
        }

        return true;
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
