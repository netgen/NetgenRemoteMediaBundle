<?php

declare(strict_types=1);

namespace Netgen\Bundle\RemoteMediaBundle\Controller\EzAdminUI\Editor;

use Cloudinary\Api\NotFound;
use Netgen\Bundle\RemoteMediaBundle\Core\FieldType\RemoteMedia\AdminInputValue;
use Netgen\Bundle\RemoteMediaBundle\Core\FieldType\RemoteMedia\UpdateFieldHelper;
use Netgen\Bundle\RemoteMediaBundle\Core\FieldType\RemoteMedia\Value;
use Netgen\Bundle\RemoteMediaBundle\RemoteMedia\Helper;
use Netgen\Bundle\RemoteMediaBundle\RemoteMedia\RemoteMediaProvider;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

final class InsertController
{
    /**
     * @var \Netgen\Bundle\RemoteMediaBundle\Core\FieldType\RemoteMedia\UpdateFieldHelper
     */
    private $updateFieldHelper;

    /**
     * @var \Netgen\Bundle\RemoteMediaBundle\RemoteMedia\RemoteMediaProvider
     */
    private $remoteMediaProvider;

    /**
     * @var \Netgen\Bundle\RemoteMediaBundle\RemoteMedia\Helper
     */
    private $remoteMediaHelper;

    public function __construct(UpdateFieldHelper $updateFieldHelper, RemoteMediaProvider $remoteMediaProvider, Helper $remoteMediaHelper)
    {
        $this->updateFieldHelper = $updateFieldHelper;
        $this->remoteMediaProvider = $remoteMediaProvider;
        $this->remoteMediaHelper = $remoteMediaHelper;
    }

    public function __invoke(Request $request): Response
    {
        $oldValue = new Value();

        if ($request->request->get('new_file') === null) {
            $oldValue = $this->remoteMediaProvider->getRemoteResource($request->request->get('resource_id'));
        }

        $hash = $request->request->all();
        $hash['new_file'] = $request->files->get('new_file');

        $adminInputValue = AdminInputValue::fromHash($hash);
        $updatedValue = $this->updateFieldHelper->updateValue($oldValue, $adminInputValue);

        $selectedVariation = $request->request->get('variation');
        $contentTypeIdentifier = $request->request->get('content_type_identifier');

        $variation = null;
        if ($selectedVariation && $contentTypeIdentifier && $updatedValue->mediaType === Value::TYPE_IMAGE) {
            $variation = $this->remoteMediaProvider->buildVariation($updatedValue, $contentTypeIdentifier, $selectedVariation);
        }

        $thumbnailUrl = null;
        $videoTag = null;
        if ($updatedValue->resourceType === 'video') {
            $thumbnailUrl = $this->remoteMediaProvider->getVideoThumbnail($updatedValue);
            $videoTag = $this->remoteMediaProvider->generateVideoTag($updatedValue, $contentTypeIdentifier, $selectedVariation);
        }

        $data = $this->remoteMediaHelper->formatBrowseItem($updatedValue);
        $data['selected_variation'] = $selectedVariation;
        $data['content_type_identifier'] = $contentTypeIdentifier;
        $data['variation_url'] = $variation->url ?? null;
        $data['image_variations'] = $adminInputValue->getVariations();
        $data['thumbnail_url'] = $thumbnailUrl;
        $data['video_tag'] = $videoTag;

        return new JsonResponse($data);
    }
}
