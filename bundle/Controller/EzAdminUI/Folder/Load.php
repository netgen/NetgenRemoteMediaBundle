<?php

declare(strict_types=1);

namespace Netgen\Bundle\RemoteMediaBundle\Controller\EzAdminUI\Folder;

use Netgen\Bundle\RemoteMediaBundle\RemoteMedia\RemoteMediaProvider;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
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

    public function __invoke(Request $request): Response
    {
        $folder = $request->query->get('folder');

        if ($folder === '(root)') {
            $folder = null;
        }

        $folders = $folder === null
            ? $this->remoteMediaProvider->listFolders()
            : $this->remoteMediaProvider->listSubFolders($folder);

        $formattedFolders = [];
        foreach ($folders as $folder) {
            $formattedFolders[] = [
                'id' => $folder['path'],
                'label' => $folder['name'],
                'children' => null,
            ];
        }

        return new JsonResponse($formattedFolders);
    }
}
