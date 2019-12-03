<?php

declare(strict_types=1);

namespace Netgen\Bundle\RemoteMediaBundle\RemoteMedia;

use Netgen\Bundle\RemoteMediaBundle\Core\FieldType\RemoteMedia\Value;

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
     * Formats browse list to comply with javascript.
     *
     * @return array
     */
    public function formatBrowseList(array $list)
    {
        $listFormatted = [];
        foreach ($list as $hit) {
            $thumbOptions = [];
            $thumbOptions['crop'] = 'fit';
            $thumbOptions['width'] = 160;
            $thumbOptions['height'] = 120;

            $value = Value::createFromCloudinaryResponse($hit);

            $mediaType = $this->determineType($hit);

            if ($mediaType === Value::TYPE_IMAGE) {
                $url = $this->provider->buildVariation($value, 'admin', 'admin_preview')->url;
                $browseUrl = $this->provider->buildVariation($value, 'admin', $thumbOptions)->url;
            } else if ($mediaType === Value::TYPE_VIDEO) {
                $url = $this->provider->getVideoThumbnail($value);
                $browseUrl = $this->provider->getVideoThumbnail($value, $thumbOptions);
            } else {
                $url = '';
                $browseUrl = '';
                // @todo
            }

            $listFormatted[] = [
                'resourceId' => $hit['public_id'],
                'tags' => $hit['tags'],
                'type' => $hit['resource_type'],
                'mediaType' => $this->determineType($hit),
                'filesize' => $hit['bytes'],
                'width' => $hit['width'],
                'height' => $hit['height'],
                'filename' => $hit['public_id'],
                'browse_url' => $browseUrl,
                'url' => $url,
            ];
        }

        return $listFormatted;
    }

    /**
     * Parse out the type, we make a difference between images, videos, and documents (pdf, doc, docx).
     *
     * @param $hit
     *
     * @return string
     */
    private function determineType($hit)
    {
        if ($hit['resource_type'] === 'video') {
            return Value::TYPE_VIDEO;
        } elseif ($hit['resource_type'] === 'image' && (!isset($hit['format']) || !\in_array($hit['format'], ['pdf', 'doc', 'docx'], true))) {
            return Value::TYPE_IMAGE;
        }

        return Value::TYPE_OTHER;
    }
}
