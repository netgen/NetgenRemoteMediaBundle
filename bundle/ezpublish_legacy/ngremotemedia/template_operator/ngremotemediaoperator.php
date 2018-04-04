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
        return array(
            'ngremotemedia',
            'ng_remote_croppable',
            'videoThumbnail',
            'ng_image_variations',
            'scaling_format',
            'is_content_browser_active',
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
            ),
            'is_content_browser_active' => array()
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
        } elseif ($operatorName === 'ng_remote_croppable') {
            $operatorValue = $this->isCroppable($namedParameters['class_identifier']);
        } elseif ($operatorName === 'videoThumbnail') {
            $operatorValue = $this->videoThumbnail($namedParameters['value']);
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
        } elseif ($operatorName === 'is_content_browser_active') {
            $operatorValue = $this->isContentBrowserActive();
        }
    }

    function isContentBrowserActive()
    {
        $container = ezpKernel::instance()->getServiceContainer();

        $cbActive = $container->getParameter('netgen_remote_media.content_browser.activated');
        $provider = $container->get('netgen_remote_media.provider');

        return $cbActive && $provider->supportsContentBrowser();

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

    function videoThumbnail($value)
    {
        $container = ezpKernel::instance()->getServiceContainer();
        $provider = $container->get( 'netgen_remote_media.provider' );

        return $provider->getVideoThumbnail($value);
    }

    function getVideoTag($value, $availableFormats, $format)
    {
        $container = ezpKernel::instance()->getServiceContainer();
        $provider = $container->get( 'netgen_remote_media.provider' );

        return $provider->generateVideoTag($value, $format, $availableFormats);
    }
}
