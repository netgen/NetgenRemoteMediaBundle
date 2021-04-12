<?php

$http = eZHTTPTool::instance();

$container = ezpKernel::instance()->getServiceContainer();
$provider = $container->get('netgen_remote_media.provider');

$parent = $http->postVariable('parent');
$folder = $http->postVariable('folder');

$folderPath = $folder;
if ($parent !== 'null') {
    $folderPath = $parent . '/' . $folderPath;
}

$provider->createFolder($folderPath);

eZExecution::cleanExit();
