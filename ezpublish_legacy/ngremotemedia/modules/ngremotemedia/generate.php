<?php

$http = eZHTTPTool::instance();

$resourceId = $http->postVariable('resourceId', '');
$variantName = $http->postVariable('name', '');
$crop_x = $http->postVariable('crop_x', 0);
$crop_y = $http->postVariable('crop_y', 0);
$crop_w = $http->postVariable('crop_w', 0);
$crop_h = $http->postVariable('crop_h', 0);

$container = ezpKernel::instance()->getServiceContainer();
$provider = $container->get( 'netgen_remote_media.provider' );

$remoteResourceValue = $provider->getRemoteResource($resourceId, 'image');

$variationCoords = array(
    $variantName => array(
        'x' => $crop_x,
        'y' => $crop_y,
        'w' => $crop_w,
        'h' => $crop_h,
    ),
);

$variations = $variationCoords + $remoteResourceValue->variations;
$remoteResourceValue->variations = $variations;

$variation = $provider->buildVariation(
    $remoteResourceValue,
    'embedded',
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
    )
);

eZHTTPTool::headerVariable('Content-Type', 'application/json; charset=utf-8');
print(json_encode($responseData));

eZExecution::cleanExit();
