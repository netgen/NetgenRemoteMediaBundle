<?php

declare(strict_types=1);

namespace Netgen\Bundle\RemoteMediaBundle\Controller\Resource;

use InvalidArgumentException;
use Netgen\RemoteMedia\API\Factory\FileHash as FileHashFactoryInterface;
use Netgen\RemoteMedia\API\ProviderInterface;
use Netgen\RemoteMedia\API\Upload\FileStruct;
use Netgen\RemoteMedia\API\Upload\ResourceStruct;
use Netgen\RemoteMedia\API\Values\Folder;
use Netgen\RemoteMedia\API\Values\RemoteResource;
use Netgen\RemoteMedia\Exception\RemoteResourceExistsException;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

use function implode;
use function in_array;

final class Upload extends AbstractController
{
    private FileHashFactoryInterface $fileHashFactory;

    public function __construct(ProviderInterface $provider, FileHashFactoryInterface $fileHashFactory)
    {
        parent::__construct($provider);

        $this->fileHashFactory = $fileHashFactory;
    }

    public function __invoke(Request $request): Response
    {
        if (!$request->files->has('file')) {
            throw new InvalidArgumentException('Missing file to upload');
        }

        $folder = $request->request->get('folder')
            ? Folder::fromPath($request->request->get('folder'))
            : null;

        $visibility = $request->request->get('visibility', RemoteResource::VISIBILITY_PUBLIC);

        if (!in_array($visibility, RemoteResource::SUPPORTED_VISIBILITIES, true)) {
            throw new InvalidArgumentException(
                'Invalid visibility option "' . $visibility . '", supported options: "' . implode('", "', RemoteResource::SUPPORTED_VISIBILITIES) . '".',
            );
        }

        /** @var \Symfony\Component\HttpFoundation\File\UploadedFile $file */
        $file = $request->files->get('file');
        $md5 = $this->fileHashFactory->createHash($file->getRealPath());
        $fileStruct = FileStruct::fromUploadedFile($file);
        $resourceStruct = new ResourceStruct(
            $fileStruct,
            'auto',
            $folder,
            $visibility,
            $request->request->get('filename'),
            $request->request->getBoolean('invalidate'),
            $request->request->getBoolean('protected'),
        );

        try {
            $resource = $this->provider->upload($resourceStruct);
            $httpCode = 200;
        } catch (RemoteResourceExistsException $e) {
            $resource = $e->getRemoteResource();
            $httpCode = $resource->getMd5() === $md5 ? 200 : 409;
        }

        return new JsonResponse($this->formatResource($resource), $httpCode);
    }
}
