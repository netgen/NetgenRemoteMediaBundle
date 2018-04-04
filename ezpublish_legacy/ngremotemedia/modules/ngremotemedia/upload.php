<?php

use Netgen\Bundle\RemoteMediaBundle\RemoteMedia\UploadFile;

$http = eZHTTPTool::instance();

$contentId = $Params['contentobject_id'];
$file = eZHTTPFile::fetch( 'file' );
$fieldId = $http->postVariable('AttributeID', '');
$contentVersionId = $http->postVariable('ContentObjectVersion', '');
$folder = $http->postVariable('folder', 'all');

$container = ezpKernel::instance()->getServiceContainer();
$provider = $container->get( 'netgen_remote_media.provider' );

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

$options = array();
if ($folder !== 'all') {
    $options['folder'] = $folder;
}

$uploadFile = UploadFile::fromZHTTPFile($file);
$value = $provider->upload($uploadFile, $options);

$attribute = eZContentObjectAttribute::fetch($fieldId, $contentVersionId);
$attribute->setAttribute('data_text', json_encode($value));
$attribute->store();

$tpl = eZTemplate::factory();
$tpl->setVariable( 'remote_value', $value );
$tpl->setVariable( 'fieldId', $fieldId );
$tpl->setVariable( 'version', $contentVersionId );
$tpl->setVariable( 'contentObjectId', $contentId );
$tpl->setVariable( 'ajax', true );

$content = $tpl->fetch('design:content/datatype/edit/ngremotemedia.tpl');

$responseData = array(
    'media' => !empty($value->resourceId) ? $value : false,
    'content' => $content
);

eZHTTPTool::headerVariable('Content-Type', 'application/json; charset=utf-8');
print(json_encode($responseData));

eZExecution::cleanExit();
