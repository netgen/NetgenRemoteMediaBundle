<?php

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
     * @param array $list
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

            $listFormatted[] = [
                'resourceId' => $hit['public_id'],
                'tags' => $hit['tags'],
                'type' => $hit['resource_type'],
                'mediaType' => $this->determineType($hit),
                'filesize' => $hit['bytes'],
                'width' => $hit['width'],
                'height' => $hit['height'],
                'filename' => $hit['public_id'],
                'url' => $this->provider->buildVariation($value, 'admin', $thumbOptions)->url,
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
        if ('video' === $hit['resource_type']) {
            return Value::TYPE_VIDEO;
        } elseif ('image' === $hit['resource_type'] && (!isset($hit['format']) || !in_array($hit['format'], ['pdf', 'doc', 'docx'], true))) {
            return Value::TYPE_IMAGE;
        }

        return Value::TYPE_OTHER;
    }
}
