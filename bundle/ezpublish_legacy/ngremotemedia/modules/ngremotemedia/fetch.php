<?php

$http = eZHTTPTool::instance();

$contentObjectId = $Params['contentobject_id'];
$attributeId = $Params['contentobjectattribute_id'];
$contentVersion = $Params['contentobject_version'];

$attribute = eZContentObjectAttribute::fetch($attributeId, $contentVersion, true);
$value = $attribute->content();

$responseData = array(
    'media' => !empty($value->resourceId) ? $value : false
);

eZHTTPTool::headerVariable('Content-Type', 'application/json; charset=utf-8');
print(json_encode($responseData));

eZExecution::cleanExit();
