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
                    'required' => false,
                    'default' => null
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
            )
        );
    }

    function modify($tpl, $operatorName, $operatorParameters, $rootNamespace, $currentNamespace, &$operatorValue, $namedParameters, $placement)
    {
        if ($operatorName === 'ngremotemedia') {
            if (!empty($namedParameters['value'])) {
                $value = $namedParameters['value'];
                $format = $namedParameters['format'] ?: '';
                $secure = $namedParameters['secure'] ?: true;

                $operatorValue = $this->ngremotemedia($value, $format, $secure);

                return;
            }

            $operatorValue = null;

            return;
        } elseif ($operatorName === 'mediaFits') {
            $operatorValue = $this->mediaFits($namedParameters['value'], $namedParameters['variations']);

            return;
        } elseif ($operatorName === 'videoThumbnail') {
            $operatorValue = $this->videoThumbnail($namedParameters['value']);
        }
    }

    function ngremotemedia($value, $format, $secure = true)
    {
        $container = ezpKernel::instance()->getServiceContainer();
        $provider = $container->get( 'netgen_remote_media.remote_media.provider' );

        // no support for named formats in legacy
        return $provider->getVariation($value, array(), $format, $secure);
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
        $provider = $container->get( 'netgen_remote_media.remote_media.provider' );

        return $provider->getVideoThumbnail($value->resourceId);
    }
}
