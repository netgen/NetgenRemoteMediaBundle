<?php

$http = eZHTTPTool::instance();

$contentId = $Params['contentobject_id'];
$fieldId = $Params['contentobjectattribute_id'];
$contentVersionId = $Params['contentobject_version'];

$variantName = $http->postVariable('name', '');
$crop_x = $http->postVariable('crop_x', 0);
$crop_y = $http->postVariable('crop_y', 0);
$crop_w = $http->postVariable('crop_w', 0);
$crop_h = $http->postVariable('crop_h', 0);

if (empty($variantName) || empty($crop_w) || empty($crop_h)) {
    eZHTTPTool::headerVariable( 'Content-Type', 'text/html; charset=utf-8' );
    print(
        json_encode(
            array(
                'error_text' => 'Missing one of the arguments: variant name, crop width, crop height',
               'content' => null,
            )
        )
    );

    eZExecution::cleanExit();
}

$container = ezpKernel::instance()->getServiceContainer();
$helper = $container->get( 'netgen_remote_media.helper' );

$value = $helper->loadValue($contentId, $fieldId, $contentVersionId);

$variationCoords = array(
    $variantName => array(
        'x' => $crop_x,
        'y' => $crop_y,
        'w' => $crop_w,
        'h' => $crop_h,
    ),
);

$variations = $variationCoords + $value->variations;
$value->variations = $variations;

$helper->updateValue($value, $contentId, $fieldId, $contentVersionId);

$variation = $helper->getVariationFromValue(
    $value,
    $variantName,
    $helper->loadAvailableFormats($contentId, $fieldId, $contentVersionId)
);

$responseData = array(
    'name' => $variantName,
    'url' => $variation->url,
    'coords' => array(
        $crop_x,
        $crop_y,
        $crop_x + $crop_w,
        $crop_y + $crop_h,
    ),
);

eZHTTPTool::headerVariable('Content-Type', 'application/json; charset=utf-8');
print(json_encode($responseData));

eZExecution::cleanExit();

?>
