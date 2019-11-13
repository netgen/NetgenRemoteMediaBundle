<?php

use Netgen\Bundle\RemoteMediaBundle\RemoteMedia\Provider\Cloudinary\Search\Query;

$http = eZHTTPTool::instance();

$container = ezpKernel::instance()->getServiceContainer();
$provider = $container->get('netgen_remote_media.provider');
$helper = $container->get( 'netgen_remote_media.helper' );

$limit = 25;
$userQuery = $http->getVariable('q', '');
$type = $http->getVariable('mediatype', 'image');
$folder = $http->getVariable('folder', 'all');
$folder = $folder !== 'all' ? $folder : null;

$nextCursor = $http->getVariable('next_cursor', null);
if ($nextCursor === 'null') {
    $nextCursor = null;
}

$searchType = $http->getVariable('search_type', 'name'); // 'name' or 'tag'

$tag = $searchType === 'name' ? '' : $userQuery;

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
    'load_more' => $results->getTotalCount() > $limit,
    'next_cursor' => $results->getNextCursor(),
];

eZHTTPTool::headerVariable('Content-Type', 'application/json; charset=utf-8');
print(json_encode($result));

eZExecution::cleanExit();
