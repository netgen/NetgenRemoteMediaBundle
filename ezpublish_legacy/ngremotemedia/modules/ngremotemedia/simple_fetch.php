<?php

$http = eZHTTPTool::instance();

$resourceId = $http->getVariable('resourceId', '');

$container = ezpKernel::instance()->getServiceContainer();
$helper = $container->get( 'netgen_remote_media.helper' );
$provider = $container->get('netgen_remote_media.provider');
$variationResolver = $container->get('netgen_remote_media.variation.resolver');

$ezoeVariationList = $variationResolver->getEmbedVariations();

$availableVersions = array();
if (!empty($ezoeVariationList)) {
    foreach ($ezoeVariationList as $aliasName => $aliasConfig) {

        foreach($aliasConfig['transformations'] as $name => $config) {
            if ($name === 'crop') {
                $availableVersions[] = array(
                    'name' => $aliasName,
                    'size' => $config,
                );
            }
        }
    }
}

$value = $provider->getRemoteResource($resourceId, 'image');

$responseData = array(
    'media' => !empty($value) ? $value: false,
    'available_versions' => $availableVersions,
    'class_list' => ''
);

eZHTTPTool::headerVariable('Content-Type', 'application/json; charset=utf-8');
print(json_encode($responseData));

eZExecution::cleanExit();
