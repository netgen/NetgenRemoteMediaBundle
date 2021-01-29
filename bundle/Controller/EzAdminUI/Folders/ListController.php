<?php

declare(strict_types=1);

namespace Netgen\Bundle\RemoteMediaBundle\Controller\EzAdminUI\Folders;

use Symfony\Component\HttpFoundation\JsonResponse;
use Netgen\Bundle\RemoteMediaBundle\RemoteMedia\RemoteMediaProvider;

final class ListController
{
    /** @var \Netgen\Bundle\RemoteMediaBundle\RemoteMedia\RemoteMediaProvider */
    private $remoteMediaProvider;

    /**
     * ListController constructor.
     */
    public function __construct(RemoteMediaProvider $remoteMediaProvider)
    {
        $this->remoteMediaProvider = $remoteMediaProvider;
    }

    public function __invoke()
    {
        $folders = $this->remoteMediaProvider->listFolders();

        $formattedFolders = [];
        foreach ($folders as $folder) {
            $folder['id'] = $folder['name'];
            unset($folder['path']);
            $formattedFolders[] = $folder;
        }

        return new JsonResponse($formattedFolders);
    }
}
