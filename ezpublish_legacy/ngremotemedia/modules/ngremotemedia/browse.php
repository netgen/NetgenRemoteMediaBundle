<?php

$http = eZHTTPTool::instance();

$container = ezpKernel::instance()->getServiceContainer();
$provider = $container->get('netgen_remote_media.provider');
$helper = $container->get( 'netgen_remote_media.helper' );

$limit = 26;
$query = $http->getVariable('q', '');
$offset = $http->getVariable('offset', 0);

$type = $http->getVariable('mediatype', 'image');

if (empty($query)) {
    $list = $provider->listResources($limit, $offset, $type);
} else {
    $list = $provider->searchResources($query, $limit, $offset, $type);
    $listByTags = $provider->searchResourcesByTag($query, $type);

    $list = array_merge($list, $listByTags);
}

$list = $helper->formatBrowseList($list);

$result = array(
    'hits' => $list
);

eZHTTPTool::headerVariable('Content-Type', 'application/json; charset=utf-8');
print(json_encode($result));

eZExecution::cleanExit();
