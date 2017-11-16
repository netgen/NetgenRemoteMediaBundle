<?php

$http = eZHTTPTool::instance();

$resourceId = $http->postVariable('resourceId', '');
$variantName = $http->postVariable('name', '');
$coords = $http->postVariable('coords', array());

$container = ezpKernel::instance()->getServiceContainer();
$provider = $container->get( 'netgen_remote_media.provider' );

$remoteResourceValue = $provider->getRemoteResource($resourceId, 'image');

$variations = $coords + $remoteResourceValue->variations;
$remoteResourceValue->variations = $variations;

$variation = $provider->buildVariation(
    $remoteResourceValue,
    'embedded',
    $variantName
);

eZHTTPTool::headerVariable('Content-Type', 'application/json; charset=utf-8');
print(json_encode(array('url' => $variation->url)));

eZExecution::cleanExit();
