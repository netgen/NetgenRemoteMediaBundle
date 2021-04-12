<?php

declare(strict_types=1);

namespace Netgen\Bundle\RemoteMediaBundle\Controller\EzAdminUI\Folder;

use Netgen\Bundle\RemoteMediaBundle\RemoteMedia\RemoteMediaProvider;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

final class Create
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
        $parent = $request->request->get('parent');
        $folder = $request->request->get('folder');

        $folderPath = $folder;
        if ($parent !== 'null') {
            $folderPath = $parent . '/' . $folderPath;
        }

        $this->remoteMediaProvider->createFolder($folderPath);

        return new Response();
    }
}
