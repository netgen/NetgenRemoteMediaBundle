<?php

declare(strict_types=1);

namespace Netgen\RemoteMedia\Core\Provider\Cloudinary\Resolver;

use Netgen\RemoteMedia\API\Upload\FileStruct;
use Netgen\RemoteMedia\API\Upload\ResourceStruct;
use Netgen\RemoteMedia\Core\Provider\Cloudinary\Converter\VisibilityType as VisibilityTypeConverter;
use Netgen\RemoteMedia\Exception\MimeCategoryParseException;
use Symfony\Component\Mime\MimeTypes;
use Symfony\Component\Mime\MimeTypesInterface;

use function count;
use function explode;
use function in_array;
use function preg_replace;
use function rtrim;

final class UploadOptions
{
    private VisibilityTypeConverter $visibilityTypeConverter;

    private array $noExtensionMimeTypes;

    private MimeTypesInterface $mimeTypes;

    public function __construct(
        VisibilityTypeConverter $visibilityTypeConverter,
        array $noExtensionMimeTypes = ['image', 'video'],
        ?MimeTypesInterface $mimeTypes = null
    ) {
        $this->visibilityTypeConverter = $visibilityTypeConverter;
        $this->noExtensionMimeTypes = $noExtensionMimeTypes;
        $this->mimeTypes = $mimeTypes ?? MimeTypes::getDefault();
    }

    public function resolve(ResourceStruct $resourceStruct): array
    {
        $clean = preg_replace(
            '#[^\\p{L}|\\p{N}]+#u',
            '_',
            $resourceStruct->getFilenameOverride() ?? $resourceStruct->getFileStruct()->getOriginalFilename(),
        );

        $cleanFileName = preg_replace('#[\\p{Z}]{2,}#u', '_', $clean);
        $fileName = rtrim($cleanFileName, '_');

        $publicId = $this->appendExtension($fileName, $resourceStruct->getFileStruct());

        if ($resourceStruct->getFolder()) {
            $publicId = $resourceStruct->getFolder()->getPath() . '/' . $publicId;
        }

        return [
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
    }

    private function appendExtension(string $publicId, FileStruct $fileStruct): string
    {
        $extension = $fileStruct->getOriginalExtension();

        if (empty($extension)) {
            return $publicId;
        }

        try {
            $mimeCategory = $this->parseMimeCategory($fileStruct->getUri());
        } catch (MimeCategoryParseException $exception) {
            return $publicId;
        }

        // cloudinary handles pdf in a weird way - it is considered an "image" but it delivers it with proper extension on download
        if ($extension !== 'pdf' && !in_array($mimeCategory, $this->noExtensionMimeTypes, true)) {
            $publicId .= '.' . $extension;
        }

        return $publicId;
    }

    /**
     * @throws \Netgen\RemoteMedia\Exception\MimeCategoryParseException
     */
    private function parseMimeCategory(string $path): string
    {
        $mimeType = $this->mimeTypes->guessMimeType($path);
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
            if (!is_string($key) || in_array($key, ['alt', 'caption', 'original_filename'])) {
                continue;
            }

            $context[$key] = $value;
        }

        return $context;
    }
}
