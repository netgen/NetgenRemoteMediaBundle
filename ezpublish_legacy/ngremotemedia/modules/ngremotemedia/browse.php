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

$searchType = $http->getVariable('search_type', 'name'); // 'name' or 'tag'

// if no query, ignore the type of the search, list everything
if (empty($userQuery) && $folder === 'all') {
    $list = $provider->listResources($limit, $offset, $type);
} else {
    $query = $folder === 'all' ? $userQuery : $folder.'/'.$userQuery;

    // search by name or by tag
    if ($searchType === 'tag') {
        $list = $provider->searchResourcesByTag($query, $type);
    } else {
        $list = $provider->searchResources($query, $limit, $offset, $type);
    }

    if ($folder === 'all') {
        $folders = $provider->listFolders();
        foreach ($folders as $folder) {
            $query = $folder['path'] . '/' . $userQuery;

            if ($searchType === 'tag') {
                $folderList = $provider->searchResourcesByTag($query, $type);
            } else {
                $folderList= $provider->searchResources($query, $limit, $offset, $type);
            }

            $list = array_merge($list, $folderList);
        }
    }
}

$loadMore = false;
if (count($list) > 25) {
    array_pop($list);
    $loadMore = true;
}

$result = array(
    'hits' => $helper->formatBrowseList($list),
    'load_more' => $loadMore
);

eZHTTPTool::headerVariable('Content-Type', 'application/json; charset=utf-8');
print(json_encode($result));

eZExecution::cleanExit();
