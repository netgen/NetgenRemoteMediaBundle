<?php

declare(strict_types=1);

namespace Netgen\RemoteMedia\Core;

use Netgen\RemoteMedia\API\Values\RemoteResource;
use function basename;
use function in_array;

/**
 * @internal
 */
final class Helper
{
    private RemoteMediaProvider $provider;

    public function __construct(RemoteMediaProvider $provider)
    {
        $this->provider = $provider;
    }

    /**
     * Formats browse item to comply with javascript.
     *
     * @return array<string, mixed>
     */
    public function formatBrowseItem(RemoteResource $resource): array
    {
        $thumbOptions = [];
        $thumbOptions['crop'] = 'fit';
        $thumbOptions['width'] = 160;
        $thumbOptions['height'] = 120;

        $mediaType = $this->determineType($resource);

        if ($mediaType === RemoteResource::TYPE_IMAGE) {
            $browseUrl = $this->provider->buildVariation($resource, 'admin', $thumbOptions)->url;
            $previewUrl = $resource->secure_url;
        } elseif ($mediaType === RemoteResource::TYPE_VIDEO) {
            $browseUrl = $this->provider->getVideoThumbnail($resource, $thumbOptions);
            $previewUrl = $this->provider->getVideoThumbnail($resource);
        } else {
            $browseUrl = '';
            $previewUrl = '';
            // @todo
        }

        return [
            'resourceId' => $resource->resourceId,
            'tags' => $resource->metaData['tags'] ?? [],
            'type' => $resource->resourceType,
            'mediaType' => $mediaType,
            'filesize' => $resource->size,
            'width' => $resource->metaData['width'] ?? null,
            'height' => $resource->metaData['height'] ?? null,
            'filename' => basename($resource->resourceId),
            'format' => $resource->metaData['format'] ?? null,
            'browse_url' => $browseUrl,
            'preview_url' => $previewUrl,
            'url' => $resource->secure_url,
            'alt_text' => $resource->metaData['alt_text'] ?? null,
        ];
    }

    /**
     * Formats browse list to comply with javascript.
     */
    public function formatBrowseList(array $list): array
    {
        $listFormatted = [];
        foreach ($list as $hit) {
            $resource = RemoteResource::createFromCloudinaryResponse($hit);

            $listFormatted[] = $this->formatBrowseItem($resource);
        }

        return $listFormatted;
    }

    /**
     * Parse out the type, we make a difference between images, videos, and documents (pdf, doc, docx).
     */
    private function determineType(RemoteResource $resource): string
    {
        if ($resource->resourceType === 'video') {
            return RemoteResource::TYPE_VIDEO;
        }
        if ($resource->resourceType === 'image' && (!isset($resource->metaData['format'])
                || !in_array($resource->metaData['format'], ['pdf', 'doc', 'docx'], true))) {
            return RemoteResource::TYPE_IMAGE;
        }

        return RemoteResource::TYPE_OTHER;
    }
}
