<?php

$http = eZHTTPTool::instance();

$container = ezpKernel::instance()->getServiceContainer();
$provider = $container->get('netgen_remote_media.provider');

$folders = $provider->listFolders();
$tags = $provider->listTags();

$formattedFolders = [];
foreach($folders as $folder) {
    $formattedFolders[] = [
        'id' => $folder['path'],
        'label' => $folder['name'],
        'children' => null,
    ];
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
