<?php

$http = eZHTTPTool::instance();

$container = ezpKernel::instance()->getServiceContainer();
$provider = $container->get('netgen_remote_media.provider');

$folders = $provider->listFolders();

$formattedFolders = array();
foreach($folders as $folder) {
    $folder['id'] = $folder['name'];
    unset($folder['path']);
    $formattedFolders[] = $folder;
}

eZHTTPTool::headerVariable('Content-Type', 'application/json; charset=utf-8');
print(json_encode($formattedFolders));

eZExecution::cleanExit();
