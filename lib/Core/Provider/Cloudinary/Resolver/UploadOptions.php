<?php

declare(strict_types=1);

namespace Netgen\RemoteMedia\Core\Provider\Cloudinary\Resolver;

use Netgen\RemoteMedia\API\Upload\FileStruct;
use Netgen\RemoteMedia\API\Upload\ResourceStruct;
use Netgen\RemoteMedia\Core\Provider\Cloudinary\CloudinaryProvider;
use Netgen\RemoteMedia\Core\Provider\Cloudinary\Converter\VisibilityType as VisibilityTypeConverter;
use Netgen\RemoteMedia\Exception\MimeCategoryParseException;
use Netgen\RemoteMedia\Exception\MimeTypeNotFoundException;
use Symfony\Component\Mime\MimeTypes;
use Symfony\Component\Mime\MimeTypesInterface;

use function count;
use function explode;
use function get_headers;
use function in_array;
use function is_string;
use function md5_file;
use function preg_replace;
use function rtrim;

final class UploadOptions
{
    public function __construct(
        private VisibilityTypeConverter $visibilityTypeConverter,
        private string $folderMode,
        private array $noExtensionMimeTypes = ['image', 'video'],
        private ?MimeTypesInterface $mimeTypes = null
    ) {
        $this->mimeTypes = $this->mimeTypes ?? MimeTypes::getDefault();
    }

    public function resolve(ResourceStruct $resourceStruct): array
    {
        $clean = preg_replace(
            '#[^\p{L}|\p{N}]+#u',
            '_',
            $resourceStruct->getFilename(),
        );

        $cleanFileName = preg_replace('#[\p{Z}]{2,}#u', '_', $clean);
        $fileName = rtrim($cleanFileName, '_');

        $publicId = $this->appendExtension($fileName, $resourceStruct->getFileStruct());

        if ($resourceStruct->doHideFilename()) {
            $publicId = md5_file($resourceStruct->getFileStruct()->getUri());
        }

        if ($resourceStruct->getFolder() && $this->folderMode === CloudinaryProvider::FOLDER_MODE_FIXED) {
            $publicId = $resourceStruct->getFolder()->getPath() . '/' . $publicId;
        }

        $options = [
            'public_id' => $publicId,
            'overwrite' => $resourceStruct->doOverwrite(),
            'invalidate' => $resourceStruct->doInvalidate() || $resourceStruct->doOverwrite(),
            'discard_original_filename' => true,
            'context' => $this->resolveContext($resourceStruct),
            'type' => $this->visibilityTypeConverter->toCloudinaryType($resourceStruct->getVisibility()),
            'resource_type' => $resourceStruct->getResourceType(),
            'access_mode' => $this->visibilityTypeConverter->toCloudinaryAccessMode($resourceStruct->getVisibility()),
            'access_control' => $this->visibilityTypeConverter->toCloudinaryAccessControl($resourceStruct->getVisibility()),
            'tags' => $resourceStruct->getTags(),
        ];

        if ($resourceStruct->getFolder() && $this->folderMode === CloudinaryProvider::FOLDER_MODE_DYNAMIC) {
            $options['folder'] = $resourceStruct->getFolder()->getPath();
        }

        return $options;
    }

    private function appendExtension(string $publicId, FileStruct $fileStruct): string
    {
        $extension = $fileStruct->getOriginalExtension();

        if ($extension === '') {
            return $publicId;
        }

        try {
            $mimeCategory = $this->resolveMimeCategory($fileStruct);
        } catch (MimeCategoryParseException $exception) {
            return $publicId;
        }

        // cloudinary handles pdf in a weird way - it is considered an "image" but it delivers it with proper extension on download
        if ($extension !== 'pdf' && !in_array($mimeCategory, $this->noExtensionMimeTypes, true)) {
            $publicId .= '.' . $extension;
        }

        return $publicId;
    }

    private function resolveMimeType(FileStruct $fileStruct): ?string
    {
        if ($fileStruct->isExternal()) {
            $headers = get_headers($fileStruct->getUri(), true);

            return $headers['content-type'] ?? $headers['Content-Type'] ?? null;
        }

        return $this->mimeTypes->guessMimeType($fileStruct->getUri());
    }

    /**
     * @throws MimeCategoryParseException
     */
    private function resolveMimeCategory(FileStruct $fileStruct): string
    {
        $mimeType = $this->resolveMimeType($fileStruct);

        if ($mimeType === null) {
            throw new MimeTypeNotFoundException($fileStruct->getUri(), $fileStruct->getType());
        }

        $parsedMime = explode('/', $mimeType);

        if (count($parsedMime) !== 2) {
            throw new MimeCategoryParseException($mimeType);
        }

        return $parsedMime[0];
    }

    /**
     * @return array<string, string>
     */
    private function resolveContext(ResourceStruct $resourceStruct): array
    {
        $context = [
            'alt' => $resourceStruct->getAltText() ?? '',
            'caption' => $resourceStruct->getCaption() ?? '',
            'original_filename' => $resourceStruct->getFilename(),
        ];

        foreach ($resourceStruct->getContext() as $key => $value) {
            if (!is_string($key) || in_array($key, ['alt', 'caption', 'original_filename'], true)) {
                continue;
            }

            $context[$key] = $value;
        }

        return $context;
    }
}
