<?php

$http = eZHTTPTool::instance();

$resourceId = $http->getVariable('resourceId', '');

$container = ezpKernel::instance()->getServiceContainer();
$provider = $container->get('netgen_remote_media.provider');
$variationResolver = $container->get('netgen_remote_media.variation.resolver');

$ezoeVariationList = $variationResolver->getEmbedVariations();

$availableVariations = array();
if (!empty($ezoeVariationList)) {
    foreach ($ezoeVariationList as $variationName => $variationConfig) {
        foreach($variationConfig['transformations'] as $name => $config) {
            if ($name !== 'crop') {
                continue;
            }

            $availableVariations[$variationName] = $config;
        }
    }
}

$value = $provider->getRemoteResource($resourceId, 'image');

$responseData = (array) $value;
$responseData['available_variations'] = $availableVariations;

$classList = $container->getParameter('netgen_remote_media.ezoe.class_list');
$responseData['class_list'] = $classList;

eZHTTPTool::headerVariable('Content-Type', 'application/json; charset=utf-8');
print(json_encode($responseData));

eZExecution::cleanExit();
