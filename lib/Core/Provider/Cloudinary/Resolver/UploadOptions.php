<?php

declare(strict_types=1);

namespace Netgen\RemoteMedia\Core\Provider\Cloudinary\Resolver;

use Netgen\RemoteMedia\API\Upload\FileStruct;
use Netgen\RemoteMedia\API\Upload\ResourceStruct;
use Netgen\RemoteMedia\Exception\MimeCategoryParseException;
use Symfony\Component\Mime\MimeTypes;
use Symfony\Component\Mime\MimeTypesInterface;
use function base_convert;
use function count;
use function explode;
use function in_array;
use function preg_replace;
use function rtrim;
use function uniqid;

final class UploadOptions
{
    private array $noExtensionMimeTypes;

    private MimeTypesInterface $mimeTypes;

    public function __construct(array $noExtensionMimeTypes = ['image', 'video'], ?MimeTypesInterface $mimeTypes = null)
    {
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

        // check if overwrite is set, if it is, do not append random string
        $overwrite = $resourceStruct->doOverwrite() ?? false;
        $invalidate = $resourceStruct->doInvalidate() ?? $overwrite;

        $publicId = $overwrite ? $fileName : $fileName . '_' . base_convert(uniqid(), 16, 36);
        $publicId = $this->appendExtension($publicId, $resourceStruct->getFileStruct());

        if ($resourceStruct->getFolder()) {
            $publicId = $resourceStruct->getFolder() . '/' . $publicId;
        }

        return [
            'public_id' => $publicId,
            'overwrite' => $overwrite,
            'invalidate' => $invalidate,
            'discard_original_filename' => true,
            'context' => [
                'alt' => $resourceStruct->getAltText() ?? '',
                'caption' => $resourceStruct->getCaption() ?? '',
            ],
            'resource_type' => $resourceStruct->getResourceType(),
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
}