<?php

declare(strict_types=1);

namespace Netgen\Bundle\RemoteMediaBundle\Controller\Folder;

use Netgen\RemoteMedia\API\ProviderInterface;
use Netgen\RemoteMedia\API\Values\Folder;
use Symfony\Component\HttpFoundation\Exception\BadRequestException;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

final class Create
{
    private ProviderInterface $provider;

    public function __construct(ProviderInterface $provider)
    {
        $this->provider = $provider;
    }

    public function __invoke(Request $request): Response
    {
        if (!$request->request->has('folder')) {
            throw new BadRequestException();
        }

        $parent = $request->request->get('parent');
        $parentFolder = $parent ? Folder::fromPath($parent) : null;

        $folder = $request->request->get('folder');

        $this->provider->createFolder($folder, $parentFolder);

        return new JsonResponse();
    }
}
