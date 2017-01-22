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
$provider = $container->get( 'netgen_remote_media.provider' );

$attribute = eZContentObjectAttribute::fetch($fieldId, $contentVersionId, true);
$value = $attribute->content();

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

$attribute = eZContentObjectAttribute::fetch($fieldId, $contentVersionId);
$attribute->setAttribute('data_text', json_encode($value));
$attribute->store();

$content = eZContentObject::fetch($contentId);

$variation = $provider->buildVariation(
    $value,
    $content->attribute('class_identifier'),
    $variantName
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
