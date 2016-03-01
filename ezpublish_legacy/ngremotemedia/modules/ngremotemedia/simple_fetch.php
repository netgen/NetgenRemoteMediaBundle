<?php

$http = eZHTTPTool::instance();

$resourceId = $Params['resource_id'];

$container = ezpKernel::instance()->getServiceContainer();
$helper = $container->get( 'netgen_remote_media.helper' );
$ezoeVariationList = $container->getParameter('netgen_remote_media.ezoe.variation_list');

$value = $helper->getValueFromRemoteResource($resourceId, 'image');

$versions = $ezoeVariationList;
$availableVersions = array();
if (!empty($versions) && is_array($versions)) {
    foreach ($versions as $version) {

        $format = explode(',', $version);

        if (count($format) != 2) {
            continue;
        }

        $size = explode('x', $format[1]);
        if (count($size) != 2) {
            continue;
        }

        /*
         * Both dimensions can't be unbound
         */
        if ($size[0] == 0 && $size[1] == 0) {
            continue;
        }

        $availableVersions[] = array(
            'name' => $format[0],
            'size' => $size,
        );
    }
}

$responseData = array(
    'media' => !empty($value) ? $value: false,
    'available_versions' => $availableVersions,
    'class_list' => $this->ezoeClassList
);

eZHTTPTool::headerVariable('Content-Type', 'application/json; charset=utf-8');
print(json_encode($responseData));

eZExecution::cleanExit();
