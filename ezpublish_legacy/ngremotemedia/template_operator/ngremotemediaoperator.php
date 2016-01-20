<?php

use Netgen\Bundle\RemoteMediaBundle\Core\FieldType\RemoteMedia\Variation;

class NgRemoteMediaOperator
{

    /**
     * @return array
     */
    function operatorList()
    {
        return array('ngremotemedia');
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
                'attribute' => array(
                    'type' => 'eZContentObjectAttribute',
                    'required' => true
                ),
                'format' => array(
                    'type' => 'mixed',
                    'required' => false,
                    'default' => null
                )
            )
        );
    }

    function modify($tpl, $operatorName, $operatorParameters, $rootNamespace, $currentNamespace, &$operatorValue, $namedParameters, $placement)
    {
        if ($operatorName === 'ngremotemedia') {
            if (!empty($namedParameters['attribute'])) {
                $attribute = $namedParameters['attribute'];
                $format = $namedParameters['format'] ?: '';
                $secure = $namedParameters['secure'] ?: true;

                $operatorValue = $this->ngremotemedia($attribute, $format, $secure);

                return;
            }

            $operatorValue = null;

            return;
        }
    }

    function ngremotemedia($attribute, $format, $secure = true)
    {
        $data = $attribute->Content;

        $container = ezpKernel::instance()->getServiceContainer();
        $provider = $container->get( 'netgen_remote_media.remote_media.provider' );

        if (array_key_exists($format, $field->value->variations)) {
            $coords = $field->value->variations[$format];
        } else {
            $coords = array('x' => 0, 'y' => 0);
        }

        $options = array(
            'secure' => $secure,
            'x' => $coords['x'],
            'y' => $coords['y'],
            'crop' => 'crop'
        );

        $variation = new Variation();
        $variation->url = $provider->getFormattedUrl($data->resourceId, $options);

        return $variation;
    }
}
