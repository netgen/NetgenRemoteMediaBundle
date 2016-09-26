<?php

$http = eZHTTPTool::instance();

$contentId = $Params['contentobject_id'];
$file = eZHTTPFile::fetch( 'file' );
$fieldId = $http->postVariable('AttributeID', '');
$contentVersionId = $http->postVariable('ContentObjectVersion', '');

$container = ezpKernel::instance()->getServiceContainer();
$helper = $container->get( 'netgen_remote_media.helper' );

if (empty($file) || empty($fieldId) || empty($contentVersionId)) {
    eZHTTPTool::headerVariable( 'Content-Type', 'text/html; charset=utf-8' );
    print(
    json_encode(
        array(
            'error_text' => 'Not all arguments where set (file, field Id, content version)',
        )
    )
    );

    eZExecution::cleanExit();
}

$value = $helper->upload(
    $file->Filename,
    pathinfo($file->OriginalFilename, PATHINFO_FILENAME),
    $fieldId,
    $contentVersionId
);

$attribute = eZContentObjectAttribute::fetch($fieldId, $contentVersionId);
$attribute->setAttribute('data_text', json_encode($value));
$attribute->store();

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

$tpl = eZTemplate::factory();
$tpl->setVariable( 'remote_value', $value );
$tpl->setVariable( 'fieldId', $fieldId );
$tpl->setVariable( 'availableFormats', $helper->loadAvailableFormats($contentId, $fieldId, $contentVersionId) );
$tpl->setVariable( 'version', $contentVersionId );
$tpl->setVariable( 'contentObjectId', $contentId );
$tpl->setVariable( 'ajax', true );

$content = $tpl->fetch('design:content/datatype/edit/ngremotemedia.tpl');

$responseData = array(
    'media' => !empty($value->resourceId) ? $value : false,
    'content' => $content,
    'toScale' => $scaling,
);

eZHTTPTool::headerVariable('Content-Type', 'application/json; charset=utf-8');
print(json_encode($responseData));

eZExecution::cleanExit();
