<?php

declare(strict_types=1);

namespace Netgen\Bundle\RemoteMediaBundle\Controller\EzAdminUI\Facets;

use Symfony\Component\HttpFoundation\JsonResponse;
use Netgen\Bundle\RemoteMediaBundle\RemoteMedia\RemoteMediaProvider;
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

        $formattedFolders = [];
        foreach($folders as $folder) {
            $formattedFolders[] = [
                'id' => $folder['path'],
                'label' => $folder['name'],
                'children' => null,
            ];
        }

        $formattedTags = [];
        foreach($tags as $tag) {
            $formattedTags[] = [
                'name' => $tag,
                'id' => $tag,
            ];
        }

        $result = [
            'folders' => $formattedFolders,
            'tags' => $formattedTags,
        ];

        return new JsonResponse($result);
    }
}
