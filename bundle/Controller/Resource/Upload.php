<?php

declare(strict_types=1);

namespace Netgen\Bundle\RemoteMediaBundle\Controller\Resource;

use InvalidArgumentException;
use Netgen\RemoteMedia\API\Upload\FileStruct;
use Netgen\RemoteMedia\API\Upload\ResourceStruct;
use Netgen\RemoteMedia\API\Values\Folder;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

final class Upload extends AbstractController
{
    public function __invoke(Request $request): Response
    {
        if (!$request->files->has('file')) {
            throw new InvalidArgumentException();
        }

        $folder = $request->request->has('folder')
            ? Folder::fromPath($request->request->get('folder'))
            : null;

        $file = $request->files->get('file');
        $fileStruct = FileStruct::fromUploadedFile($file);
        $resourceStruct = new ResourceStruct(
            $fileStruct,
            'auto',
            $folder,
            $request->request->get('filename'),
            $request->request->getBoolean('overwrite', false),
            $request->request->getBoolean('invalidate', false),
        );

        $resource = $this->provider->upload($resourceStruct);

        return new JsonResponse($this->formatResource($resource));
    }
}
