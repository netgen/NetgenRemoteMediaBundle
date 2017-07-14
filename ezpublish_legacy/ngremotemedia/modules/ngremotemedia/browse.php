<?php

$http = eZHTTPTool::instance();

$contentObjectId = $Params['contentobject_id'];
$attributeId = $Params['contentobjectattribute_id'];
$contentVersion = $Params['contentobject_version'];

$container = ezpKernel::instance()->getServiceContainer();
$provider = $container->get('netgen_remote_media.provider');
$helper = $container->get( 'netgen_remote_media.helper' );

$limit = 25;
$query = $http->getVariable('q', '');
$offset = $http->getVariable('offset', 0);

if (empty($query)) {
    $list = $provider->listResources($limit, $offset);
} else {
    $list = $provider->searchResources($query, $limit, $offset);
    $listByTags = $provider->searchResourcesByTag($query);

    $list = array_merge($list, $listByTags);
}

$list = $helper->formatBrowseList($list);

$result = array(
    'hits' => $list,
    'count' => $provider->countResources(), //@todo: introduce searchCount method
);

eZHTTPTool::headerVariable('Content-Type', 'application/json; charset=utf-8');
print(json_encode($result));

eZExecution::cleanExit();
