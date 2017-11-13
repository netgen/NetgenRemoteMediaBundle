<?php

$http = eZHTTPTool::instance();

$container = ezpKernel::instance()->getServiceContainer();
$provider = $container->get('netgen_remote_media.provider');
$helper = $container->get( 'netgen_remote_media.helper' );

$limit = 26;
$userQuery = $http->getVariable('q', '');
$offset = $http->getVariable('offset', 0);

$type = $http->getVariable('mediatype', 'image');
$folder = $http->getVariable('folder', 'all');

if (empty($userQuery) && $folder === 'all') {
    $list = $provider->listResources($limit, $offset, $type);
} else {
    $query = $folder === 'all' ? $userQuery : $folder.'/'.$userQuery;

    $list = $provider->searchResources($query, $limit, $offset, $type);
    $listByTags = $provider->searchResourcesByTag($query, $type);

    $list = array_merge($list, $listByTags);

    if ($folder === 'all') {
        $folders = $provider->listFolders();
        foreach ($folders as $folder) {
            $query = $folder['path'] . '/' . $userQuery;
            $listFolders = $provider->searchResources($query, $limit, $offset, $type);
            $listByTags = $provider->searchResourcesByTag($query, $type);

            $list = array_merge($list, $listFolders, $listByTags);
        }
    }
}

$result = array(
    'hits' => $helper->formatBrowseList($list)
);

eZHTTPTool::headerVariable('Content-Type', 'application/json; charset=utf-8');
print(json_encode($result));

eZExecution::cleanExit();
