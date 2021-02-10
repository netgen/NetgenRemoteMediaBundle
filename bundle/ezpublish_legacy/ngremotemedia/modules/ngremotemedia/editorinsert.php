<?php

use Symfony\Component\HttpFoundation\File\UploadedFile;
use Netgen\Bundle\RemoteMediaBundle\Core\FieldType\RemoteMedia\AdminInputValue;
use Netgen\Bundle\RemoteMediaBundle\Core\FieldType\RemoteMedia\Value;

$http = eZHTTPTool::instance();

$container = ezpKernel::instance()->getServiceContainer();
$updateFieldHelper = $container->get('netgen_remote_media.admin.field_update.helper');
$remoteMediaProvider = $container->get('netgen_remote_media.provider');

$oldValue = new Value();

$hash = [
    'resource_id' => $http->postVariable('resource_id', ''),
    'alt_text' => $http->postVariable('alt_text', ''),
    'tags' => $http->postVariable('tags', []),
    'image_variations' => $http->postVariable('image_variations', '{}'),
    'new_file' => null,
];

if (!is_array($hash['tags'])) {
    $hash['tags'] = [];
}

if (isset($_FILES['new_file'])) {
    $file = $_FILES['new_file'];

    $hash['new_file'] = new UploadedFile(
        $file['tmp_name'],
        $file['name'],
        $file['type'],
        $file['size'],
        $file['error']
    );
}

$adminInputValue = AdminInputValue::fromHash($hash);

$updatedValue = $updateFieldHelper->updateValue($oldValue, $adminInputValue);

$variation = $http->postVariable('variation');
$contentTypeIdentifier = $http->postVariable('content_type_identifier');

if ($variation && $contentTypeIdentifier) {
    $variation = $remoteMediaProvider->buildVariation($updatedValue, $contentTypeIdentifier, $variation);
}

$result = [
    'resource_id' => $updatedValue->resourceId,
    'type' => $updatedValue->mediaType,
    'url' => $variation ? $variation->url : $updatedValue->secure_url,
    'metadata' => $updatedValue->metaData,
];

eZHTTPTool::headerVariable('Content-Type', 'application/json; charset=utf-8');
print(json_encode($result));

eZExecution::cleanExit();
