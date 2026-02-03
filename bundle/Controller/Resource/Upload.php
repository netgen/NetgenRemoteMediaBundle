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
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Contracts\Translation\TranslatorInterface;

use function fclose;
use function fopen;
use function fread;
use function fseek;
use function implode;
use function in_array;
use function is_array;
use function is_file;
use function is_readable;
use function strpos;
use function strtolower;

use const SEEK_END;

final class Upload extends AbstractController
{
    private FileHashFactoryInterface $fileHashFactory;

    private TranslatorInterface $translator;

    public function __construct(
        ProviderInterface $provider,
        FileHashFactoryInterface $fileHashFactory,
        TranslatorInterface $translator,
    ) {
        parent::__construct($provider);

        $this->fileHashFactory = $fileHashFactory;
        $this->translator = $translator;
    }

    public function __invoke(Request $request): Response
    {
        if (!$request->files->has('file')) {
            throw new InvalidArgumentException(
                $this->translator->trans(
                    'ngrm.edit.vue.upload.error.missing_file',
                    [],
                    'ngremotemedia',
                ),
            );
        }

        $uploadContext = $request->request->all()['upload_context'] ?? [];
        $uploadContext = is_array($uploadContext) ? $uploadContext : [];

        $folder = $request->request->get('folder') && $request->request->get('folder') !== 'null'
            ? Folder::fromPath($request->request->get('folder'))
            : null;

        $visibility = $request->request->get('visibility', RemoteResource::VISIBILITY_PUBLIC);

        if (!in_array($visibility, RemoteResource::SUPPORTED_VISIBILITIES, true)) {
            throw new InvalidArgumentException(
                $this->translator->trans(
                    'ngrm.edit.vue.upload.error.invalid_visibility',
                    [
                        '%visibility%' => $visibility,
                        '%supported_visibilities%' => implode('", "', $this->provider->getSupportedVisibilities()),
                    ],
                    'ngremotemedia',
                ),
            );
        }

        /** @var UploadedFile $file */
        $file = $request->files->get('file');

        if (!$file->isFile()) {
            throw new InvalidArgumentException(
                $this->translator->trans(
                    'ngrm.edit.vue.upload.error.file_upload_failed',
                    [],
                    'ngremotemedia',
                ),
            );
        }

        $md5 = $this->fileHashFactory->createHash($file->getRealPath());
        $fileStruct = FileStruct::fromUploadedFile($file);

        $resourceType = $this->isEncryptedPdf($file) ? 'raw' : 'auto';
        $resourceStruct = new ResourceStruct(
            $fileStruct,
            $resourceType,
            $folder,
            $visibility,
            $request->request->get('filename'),
            $request->request->getBoolean('overwrite'),
            $request->request->getBoolean('overwrite'),
            null,
            null,
            [],
            $uploadContext,
            $request->request->getBoolean('hide_filename'),
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

    private function isEncryptedPdf(UploadedFile $file): bool
    {
        if (strtolower($file->getClientOriginalExtension()) !== 'pdf') {
            return false;
        }

        $path = (string) $file->getRealPath();
        if ($path === '' || !is_file($path) || !is_readable($path)) {
            return false;
        }

        $fp = @fopen($path, 'r');
        if ($fp === false) {
            return false;
        }

        $head = (string) fread($fp, 4096);

        @fseek($fp, -16384, SEEK_END);
        $tail = (string) fread($fp, 16384);

        fclose($fp);

        if (strpos($head, '%PDF-') !== 0) {
            return false;
        }

        return strpos($head . $tail, '/Encrypt') !== false;
    }
}
