<?php

$http = eZHTTPTool::instance();

$contentObjectId = $Params['contentobject_id'];
$attributeId = $Params['contentobjectattribute_id'];
$contentVersion = $Params['contentobject_version'];

$container = ezpKernel::instance()->getServiceContainer();
$helper = $container->get( 'netgen_remote_media.helper' );
$configResolver = $container->get('ezpublish.config.resolver');
$browseLimit = $configResolver->getParameter('ezexceed.browse_limit', 'netgen_remote_media');

$limit = 25;
$query = $http->getVariable('q', '');
$offset = $http->getVariable('offset', 0);

$list = $helper->searchResources($query, $offset, $limit, $browseLimit);

eZHTTPTool::headerVariable('Content-Type', 'application/json; charset=utf-8');
print(json_encode($list));

eZExecution::cleanExit();
