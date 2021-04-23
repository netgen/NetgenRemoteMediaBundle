<?php

use Netgen\Bundle\RemoteMediaBundle\RemoteMedia\Provider\Cloudinary\Search\Query;

$http = eZHTTPTool::instance();

$container = ezpKernel::instance()->getServiceContainer();
$provider = $container->get('netgen_remote_media.provider');
$helper = $container->get( 'netgen_remote_media.helper' );

$limit = 25;
$userQuery = $http->getVariable('q', '');
$tag = $http->getVariable('tag', 'all');
$type = $http->getVariable('mediatype', 'all');
$folder = $http->getVariable('folder', 'all');
$type = $type !== 'all' ? $type : null;
$tag = $tag !== 'all' ? $tag : null;

switch ($folder) {
    case '(all)':
        $folder = null;
        break;
    case '(root)':
        $folder = '';
        break;
}

$nextCursor = $http->getVariable('next_cursor', null);
if ($nextCursor === 'null') {
    $nextCursor = null;
}

$query = new Query(
    $userQuery,
    $type,
    $limit,
    $folder,
    $tag,
    $nextCursor
);

$results = $provider->searchResources($query);
$list = $results->getResults();

$result = [
    'hits' => $helper->formatBrowseList($list),
    'load_more' => $results->getNextCursor() !== null,
    'next_cursor' => $results->getNextCursor(),
];

eZHTTPTool::headerVariable('Content-Type', 'application/json; charset=utf-8');
print(json_encode($result));

eZExecution::cleanExit();
