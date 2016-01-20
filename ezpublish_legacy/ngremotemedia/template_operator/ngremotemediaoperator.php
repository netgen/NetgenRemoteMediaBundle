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

        return $provider->getVariation($data, array(), $format, $secure);
    }
}
