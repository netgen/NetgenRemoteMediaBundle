<?php

$http = eZHTTPTool::instance();

$contentId = $Params['contentobject_id'];
$fieldId = $Params['contentobjectattribute_id'];
$contentVersionId = $Params['contentobject_version'];

$resourceId = $http->postVariable('resource_id', '');
$languageCode = $http->postVariable('language_code', null);

if (empty($resourceId)) {
    eZHTTPTool::headerVariable( 'Content-Type', 'text/html; charset=utf-8' );
    print(
        json_encode(
            array(
                'error_text' => 'Resource id must not be empty',
                'content' => null,
            )
        )
    );
    eZExecution::cleanExit();
}

$container = ezpKernel::instance()->getServiceContainer();
$provider = $container->get( 'netgen_remote_media.provider' );

$updatedValue = $provider->getRemoteResource($resourceId, 'image');
$attribute = eZContentObjectAttribute::fetch($fieldId, $contentVersionId);
$attribute->setAttribute('data_text', json_encode($updatedValue));
$attribute->store();

$tpl = eZTemplate::factory();
$tpl->setVariable( 'remote_value', $updatedValue );
$tpl->setVariable( 'fieldId', $fieldId );
$tpl->setVariable( 'version', $contentVersionId );
$tpl->setVariable( 'contentObjectId', $contentId );
$tpl->setVariable( 'ajax', true );

$content = $tpl->fetch('design:content/datatype/edit/ngremotemedia.tpl');

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
    'media' => !empty($updatedValue->resourceId) ? $updatedValue : false,
    'content' => $content,
    'toScale' => $scaling,
);

eZHTTPTool::headerVariable('Content-Type', 'application/json; charset=utf-8');
print(json_encode($responseData));

eZExecution::cleanExit();
