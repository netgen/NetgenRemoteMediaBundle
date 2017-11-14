<?php

$http = eZHTTPTool::instance();

$container = ezpKernel::instance()->getServiceContainer();
$provider = $container->get( 'netgen_remote_media.provider' );

//$contentId = $Params['contentobject_id'];
$fieldId = $Params['contentobjectattribute_id'];
$contentVersionId = $Params['contentobject_version'];

$attribute = eZContentObjectAttribute::fetch($fieldId, $contentVersionId, true);
$value = $attribute->content();

$variations = $http->postVariable('variations');
$variations = json_decode($variations);
$variationCoords = array();
foreach ($variations as $variation) {
    $variantName = $variation['name'];
    $crop_x = $variation['crop_x'];
    $crop_y = $variation['crop_y'];
    $crop_w = $variation['crop_w'];
    $crop_h = $variation['crop_h'];

    if (empty($variantName) || empty($crop_w) || empty($crop_h)) {
        eZHTTPTool::headerVariable( 'Content-Type', 'text/html; charset=utf-8' );
        print(
        json_encode(
            array(
                'error_text' => 'Missing one of the arguments: variant name, crop width, crop height for one of the submitted variations',
                'content' => null,
            )
        )
        );

        eZExecution::cleanExit();
    }

    $variationCoords[$variantName] = array(
        'x' => $crop_x,
        'y' => $crop_y,
        'w' => $crop_w,
        'h' => $crop_h,
    );
}

$variations = $variationCoords + $value->variations;
$value->variations = $variations;

$attribute = eZContentObjectAttribute::fetch($fieldId, $contentVersionId);
$attribute->setAttribute('data_text', json_encode($value));
$attribute->store();

eZHTTPTool::headerVariable('Content-Type', 'application/json; charset=utf-8');
eZExecution::cleanExit();
