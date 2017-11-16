<?php

$http = eZHTTPTool::instance();

$file = eZHTTPFile::fetch( 'file' );
$fieldId = $http->postVariable('AttributeID', '');
$contentVersionId = $http->postVariable('ContentObjectVersion', '');
$contentId = $http->postVariable('ContentObjectId', '');

if (empty($file) || empty($fieldId) || empty($contentId) || empty($contentVersionId)) {
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

$container = ezpKernel::instance()->getServiceContainer();
$provider = $container->get( 'netgen_remote_media.provider' );

$value = $provider->upload(
    $file->Filename,
    pathinfo($file->OriginalFilename, PATHINFO_FILENAME)
);

$responseData = array(
    'media' => !empty($value->resourceId) ? $value : false,
);

$storage = $container->get('ezpublish.fieldType.ngremotemedia.storage_gateway');
$storage->storeFieldData($fieldId,$value->resourceId, $contentId, $provider->getIdentifier(), $contentVersionId);

eZHTTPTool::headerVariable('Content-Type', 'application/json; charset=utf-8');
print(json_encode($responseData));

eZExecution::cleanExit();
