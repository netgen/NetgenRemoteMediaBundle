<?php

$http = eZHTTPTool::instance();

$container = ezpKernel::instance()->getServiceContainer();
$provider = $container->get('netgen_remote_media.provider');

$folders = $provider->listFolders();
$tags = $provider->listTags();

$formattedFolders = [];
foreach($folders as $folder) {
    $folder['id'] = $folder['name'];
    unset($folder['path']);
    $formattedFolders[] = $folder;
}

$formattedTags = [];
foreach($tags as $tag) {
   $formattedTags[] = [
       'name' => $tag,
       'id' => $tag,
   ];
}

$result = [
    'folders' => $formattedFolders,
    'tags' => $formattedTags,
];

eZHTTPTool::headerVariable('Content-Type', 'application/json; charset=utf-8');
print(json_encode($result));

eZExecution::cleanExit();
