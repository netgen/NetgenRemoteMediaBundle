<?php

declare(strict_types=1);

namespace Netgen\Bundle\RemoteMediaBundle\Controller\Folder;

use Netgen\RemoteMedia\API\ProviderInterface;
use Netgen\RemoteMedia\API\Values\Folder;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

final class Load
{
    private ProviderInterface $provider;

    public function __construct(ProviderInterface $provider)
    {
        $this->provider = $provider;
    }

    public function __invoke(Request $request): Response
    {
        $parent = $request->query->get('folder') ? Folder::fromPath($request->query->get('folder')) : null;

        $folders = $this->provider->listFolders($parent);
        $formattedFolders = [];

        /** @var \Netgen\RemoteMedia\API\Values\Folder $folder */
        foreach ($folders as $folder) {
            $formattedFolders[] = [
                'id' => $folder->getPath(),
                'label' => $folder->getName(),
                'children' => null,
            ];
        }

        return new JsonResponse($formattedFolders);
    }
}
