<?php

declare(strict_types=1);

namespace Netgen\Bundle\RemoteMediaBundle\Controller\EzAdminUI;

use Netgen\Bundle\RemoteMediaBundle\Core\FieldType\RemoteMedia\AdminInputValue;
use Netgen\Bundle\RemoteMediaBundle\Core\FieldType\RemoteMedia\UpdateFieldHelper;
use Netgen\Bundle\RemoteMediaBundle\Core\FieldType\RemoteMedia\Value;
use Netgen\Bundle\RemoteMediaBundle\RemoteMedia\RemoteMediaProvider;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

final class EditorInsertController
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
     * EditorInsertController constructor
     *
     * @param \Netgen\Bundle\RemoteMediaBundle\Core\FieldType\RemoteMedia\UpdateFieldHelper $updateFieldHelper
     * @param \Netgen\Bundle\RemoteMediaBundle\RemoteMedia\RemoteMediaProvider $remoteMediaProvider
     */
    public function __construct(UpdateFieldHelper $updateFieldHelper, RemoteMediaProvider $remoteMediaProvider)
    {
        $this->updateFieldHelper = $updateFieldHelper;
        $this->remoteMediaProvider = $remoteMediaProvider;
    }

    public function __invoke(Request $request): Response
    {
        $oldValue = new Value();
        $adminInputValue = AdminInputValue::fromHash($request->request->all());

        $updatedValue = $this->updateFieldHelper->updateValue($oldValue, $adminInputValue);

        $variation = $request->request->get('variation');
        $contentTypeIdentifier = $request->request->get('content_type_identifier');

        if ($variation && $contentTypeIdentifier) {
            $variation = $remoteMediaProvider->buildVariation($updatedValue, $contentTypeIdentifier, $variation);
        }

        return new JsonResponse([
            'resource_id' => $updatedValue->resourceId,
            'type' => $updatedValue->mediaType,
            'url' => $variation ? $variation->url : $updatedValue->secure_url,
            'metadata' => $updatedValue->metaData,
        ]);
    }
}
