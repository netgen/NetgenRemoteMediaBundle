<?php

$http = eZHTTPTool::instance();

$container = ezpKernel::instance()->getServiceContainer();
$provider = $container->get('netgen_remote_media.provider');

$folder = $http->getVariable('folder');

if ($folder === '(root)') {
    $folder = null;
}

$folders = $folder === null
    ? $provider->listFolders()
    : $provider->listSubFolders($folder);

$formattedFolders = [];
foreach($folders as $folder) {
    $formattedFolders[] = [
        'id' => $folder['path'],
        'label' => $folder['name'],
        'children' => null,
    ];
}

eZHTTPTool::headerVariable('Content-Type', 'application/json; charset=utf-8');
print(json_encode($formattedFolders));

eZExecution::cleanExit();
