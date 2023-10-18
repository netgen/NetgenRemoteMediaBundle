<?php

declare(strict_types=1);

namespace Netgen\Bundle\RemoteMediaBundle\Controller\EzAdminUI\Facets;

use Netgen\Bundle\RemoteMediaBundle\RemoteMedia\RemoteMediaProvider;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;

final class Load
{
    /**
     * @var \Netgen\Bundle\RemoteMediaBundle\RemoteMedia\RemoteMediaProvider
     */
    private $remoteMediaProvider;

    public function __construct(RemoteMediaProvider $remoteMediaProvider)
    {
        $this->remoteMediaProvider = $remoteMediaProvider;
    }

    public function __invoke(): Response
    {
        $folders = $this->remoteMediaProvider->listFolders();
        $tags = $this->remoteMediaProvider->listTags();
        $metadataFields = $this->remoteMediaProvider->listMetadataFields();

        $formattedFolders = [];
        foreach ($folders as $folder) {
            $formattedFolders[] = [
                'id' => $folder['path'],
                'label' => $folder['name'],
                'children' => null,
            ];
        }

        $formattedTags = [];
        foreach ($tags as $tag) {
            $formattedTags[] = [
                'name' => $tag,
                'id' => $tag,
            ];
        }

        $formattedMetadataFields = [];
        foreach ($metadataFields as $metadataField) {
            $formattedMetadataFields[] = [
                'id' => $metadataField['external_id'],
                'label' => $metadataField['label'],
            ];
        }

        $result = [
            'folders' => $formattedFolders,
            'tags' => $formattedTags,
            'metadataFields' => $formattedMetadataFields,
        ];

        return new JsonResponse($result);
    }
}
