<?php

$http = eZHTTPTool::instance();

$contentObjectId = $Params['contentobject_id'];
$attributeId = $Params['contentobjectattribute_id'];
$contentVersion = $Params['contentobject_version'];

$attribute = eZContentObjectAttribute::fetch($attributeId, $contentVersion, true);
$value = $attribute->content();

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

$responseData = array(
    'media' => !empty($value->resourceId) ? $value : false,
    //'content' => $content,
    'toScale' => $scaling,
);

eZHTTPTool::headerVariable('Content-Type', 'application/json; charset=utf-8');
print(json_encode($responseData));

eZExecution::cleanExit();

?>
