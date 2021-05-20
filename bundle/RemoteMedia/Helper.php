<?php

declare(strict_types=1);

namespace Netgen\Bundle\RemoteMediaBundle\RemoteMedia;

use Netgen\Bundle\RemoteMediaBundle\Core\FieldType\RemoteMedia\Value;

use function in_array;
use function basename;

/**
 * Class Helper.
 *
 * @internal
 */
class Helper
{
    /**
     * @var \Netgen\Bundle\RemoteMediaBundle\RemoteMedia\RemoteMediaProvider
     */
    protected $provider;

    /**
     * Helper constructor.
     *
     * @param \Netgen\Bundle\RemoteMediaBundle\RemoteMedia\RemoteMediaProvider $provider
     */
    public function __construct(
        RemoteMediaProvider $provider
    ) {
        $this->provider = $provider;
    }

    /**
     * Formats browse item to comply with javascript.
     *
     * @param \Netgen\Bundle\RemoteMediaBundle\Core\FieldType\RemoteMedia\Value $value
     *
     * @return array
     */
    public function formatBrowseItem(Value $value)
    {
        $thumbOptions = [];
        $thumbOptions['crop'] = 'fit';
        $thumbOptions['width'] = 160;
        $thumbOptions['height'] = 120;

        $mediaType = $this->determineType($value);

        if ($mediaType === Value::TYPE_IMAGE) {
            $browseUrl = $this->provider->buildVariation($value, 'admin', $thumbOptions)->url;
            $previewUrl = $value->secure_url;
        } else if ($mediaType === Value::TYPE_VIDEO) {
            $browseUrl = $this->provider->getVideoThumbnail($value, $thumbOptions);
            $previewUrl = $this->provider->getVideoThumbnail($value);
        } else {
            $browseUrl = '';
            $previewUrl = '';
            // @todo
        }

        return [
            'resourceId' => $value->resourceId,
            'tags' => $value->metaData['tags'] ?? [],
            'type' => $value->resourceType,
            'mediaType' => $mediaType,
            'filesize' => $value->size,
            'width' => $value->metaData['width'] ?? null,
            'height' => $value->metaData['height'] ?? null,
            'filename' => basename($value->resourceId),
            'format' => $value->metaData['format'] ?? null,
            'browse_url' => $browseUrl,
            'preview_url' => $previewUrl,
            'url' => $value->secure_url,
            'alt_text' => $value->metaData['alt_text'] ?? null,
        ];
    }

    /**
     * Formats browse list to comply with javascript.
     *
     * @param array $list
     *
     * @return array
     */
    public function formatBrowseList(array $list)
    {
        $listFormatted = [];
        foreach ($list as $hit) {
            $value = Value::createFromCloudinaryResponse($hit);

            $listFormatted[] = $this->formatBrowseItem($value);
        }

        return $listFormatted;
    }

    /**
     * Parse out the type, we make a difference between images, videos, and documents (pdf, doc, docx).
     *
     * @param \Netgen\Bundle\RemoteMediaBundle\Core\FieldType\RemoteMedia\Value $value
     *
     * @return string
     */
    private function determineType(Value $value)
    {
        if ($value->resourceType === 'video') {
            return Value::TYPE_VIDEO;
        } elseif ($value->resourceType === 'image' && (!isset($value->metaData['format']) || !in_array($value->metaData['format'], ['pdf', 'doc', 'docx'], true))) {
            return Value::TYPE_IMAGE;
        }

        return Value::TYPE_OTHER;
    }
}
