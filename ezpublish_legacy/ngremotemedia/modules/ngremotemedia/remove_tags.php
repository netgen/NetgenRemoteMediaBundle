<?php

$http = eZHTTPTool::instance();

$contentId = $Params['contentobject_id'];
$fieldId = $Params['contentobjectattribute_id'];
$contentVersionId = $Params['contentobject_version'];

$resourceId = $http->variable('id', '');
$tag = $http->postVariable('tag', null);

if (empty($resourceId) || empty($tag)) {
    eZHTTPTool::headerVariable( 'Content-Type', 'text/html; charset=utf-8' );
    print(
        json_encode(
            array(
                'error_text' => 'Resource id must not be empty',
            )
        )
    );
    eZExecution::cleanExit();
}

$container = ezpKernel::instance()->getServiceContainer();
$helper = $container->get( 'netgen_remote_media.helper' );

$tags = $helper->removeTag($contentId, $fieldId, $contentVersionId, $tag);

eZHTTPTool::headerVariable('Content-Type', 'application/json; charset=utf-8');
print(json_encode($tags));

eZExecution::cleanExit();
