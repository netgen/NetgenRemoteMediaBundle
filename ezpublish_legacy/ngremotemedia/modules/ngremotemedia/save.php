<?php

$http = eZHTTPTool::instance();

$container = ezpKernel::instance()->getServiceContainer();
$provider = $container->get( 'netgen_remote_media.provider' );

$fieldId = $Params['contentobjectattribute_id'];
$contentVersionId = $Params['contentobject_version'];

$attribute = eZContentObjectAttribute::fetch($fieldId, $contentVersionId, true);
$value = $attribute->content();

$variations = $http->postVariable('variations');
$variationCoords = array();
foreach ($variations as $variationName => $coordinates) {
    $variationCoords[$variationName] = array(
        'x' => $coordinates['x'],
        'y' => $coordinates['y'],
        'w' => $coordinates['w'],
        'h' => $coordinates['h'],
    );
}

$variations = $variationCoords + $value->variations;
$value->variations = $variations;

$attribute = eZContentObjectAttribute::fetch($fieldId, $contentVersionId);
$attribute->setAttribute('data_text', json_encode($value));
$attribute->store();

eZHTTPTool::headerVariable('Content-Type', 'application/json; charset=utf-8');

print(json_encode(array()));

eZExecution::cleanExit();
