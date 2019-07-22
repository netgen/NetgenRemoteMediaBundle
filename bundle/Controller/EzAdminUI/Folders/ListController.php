<?php

declare(strict_types=1);


namespace Netgen\Bundle\RemoteMediaBundle\Controller\EzAdminUI\Folders;


use Symfony\Component\HttpFoundation\JsonResponse;

final class ListController
{
    /** @var \Netgen\Bundle\RemoteMediaBundle\RemoteMedia\RemoteMediaProvider */
    private $remoteMediaProvider;

    /**
     * ListController constructor.
     *
     * @param \Netgen\Bundle\RemoteMediaBundle\RemoteMedia\RemoteMediaProvider $remoteMediaProvider
     */
    public function __construct(\Netgen\Bundle\RemoteMediaBundle\RemoteMedia\RemoteMediaProvider $remoteMediaProvider)
    {
        $this->remoteMediaProvider = $remoteMediaProvider;
    }

    public function __invoke()
    {
        $folders = $this->remoteMediaProvider->listFolders();

        $formattedFolders = array();
        foreach($folders as $folder) {
            $folder['id'] = $folder['name'];
            unset($folder['path']);
            $formattedFolders[] = $folder;
        }

        return new JsonResponse($formattedFolders);
    }
}
