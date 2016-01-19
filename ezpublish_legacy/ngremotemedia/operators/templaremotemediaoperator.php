<?php

/**
 * @author Kristian Blom
 * @since 2012-01-02
 */
class TemplateRemotemediaOperator
{

    /**
     * @return array
     */
    function operatorList()
    {
        return array('remotemedia');
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
            'remotemedia' => array(
                'attribute' => array(
                    'type' => 'eZContentObjectAttribute',
                    'required' => true
                ),
                'format' => array(
                    'type' => 'mixed',
                    'required' => false,
                    'default' => null
                    ),
                'quality' => array(
                    'type' => 'mixed',
                    'required' => false,
                    'default' => null
                ),
                'fetchInfo' => array(
                    'type' => 'mixed',
                    'required' => false,
                    'default' => null
                )
            )
        );
    }


    /**
     * @param $tpl
     * @param $operatorName
     * @param $operatorParameters
     * @param $rootNamespace
     * @param $currentNamespace
     * @param $operatorValue
     * @param $namedParameters
     * @param $placement
     * @return void
     */
    function modify($tpl, $operatorName, $operatorParameters, $rootNamespace, $currentNamespace, &$operatorValue, $namedParameters, $placement)
    {
        $attr = $namedParameters['attribute'];
        if (!$attr) {
            $operatorValue = null;
            return;
        }
        $format = $namedParameters['format'];
        $quality = $namedParameters['quality'];
        $format = $format ?: array(300, 200);

        switch ($operatorName) {
            case 'remotemedia':
                $handler = $attr->content();
                $operatorValue = $handler->media($format, $quality);
                break;
        }
    }
}
