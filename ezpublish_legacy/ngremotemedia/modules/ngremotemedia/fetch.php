<?php

$http = eZHTTPTool::instance();

$contentObjectId = $Params['contentobject_id'];
$attributeId = $Params['contentobjectattribute_id'];
$contentVersion = $Params['contentobject_version'];

$container = ezpKernel::instance()->getServiceContainer();
$helper = $container->get( 'netgen_remote_media.helper' );

$value = $helper->loadValue($contentObjectId, $attributeId, $contentVersion);

$variations = $value->variations;

$scaling = array();
foreach ($variations as $name => $coords) {
    $scaling[] = array(
        'name' => $name,
        'coords' => array(
            (int) $coords['x'],
            (int) $coords['y'],
            (int) $coords['x'] + (int) $coords['w'],
            (int) $coords['y'] + (int) $coords['h'],
        ),
    );
}

//$tpl = eZTemplate::factory();
//$tpl->setVariable('value', $value);
//$tpl->setVariable('fieldId', $attributeId);
//$tpl->setVariable('availableFormats', $helper->loadAvailableFormats($contentObjectId, $attributeId, $contentVersion));

//$content = $tpl->fetch('design:mymodule/myview.tpl');

$responseData = array(
    'media' => !empty($value->resourceId) ? $value : false,
    //'content' => $content,
    'toScale' => $scaling,
);

eZHTTPTool::headerVariable('Content-Type', 'application/json; charset=utf-8');
print(json_encode($responseData));

eZExecution::cleanExit();

?>
