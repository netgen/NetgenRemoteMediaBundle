<?php

namespace Netgen\Bundle\RemoteMediaBundle\RemoteMedia;

use Netgen\Bundle\RemoteMediaBundle\Core\FieldType\RemoteMedia\Value;

/**
 * Class Helper
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
     * Parse out the type, we make a difference between images, videos, and documents (pdf, doc, docx).
     *
     * @param $hit
     *
     * @return string
     */
    private function determineType($hit)
    {
        if ($hit['resource_type'] == 'video') {
            return Value::TYPE_VIDEO;
        } else if ($hit['resource_type'] == 'image' && (!isset($hit['format']) || !in_array($hit['format'], array('pdf', 'doc', 'docx')))) {
            return Value::TYPE_IMAGE;
        }

        return Value::TYPE_OTHER;
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
        $listFormatted = array();
        foreach ($list as $hit) {
            $thumbOptions = array();
            $thumbOptions['crop'] = 'fit';
            $thumbOptions['width'] = 160;
            $thumbOptions['height'] = 120;

            $value = Value::createFromCloudinaryResponse($hit);

            $listFormatted[] = array(
                'resourceId' => $hit['public_id'],
                'tags' => $hit['tags'],
                'type' => $hit['resource_type'],
                'mediaType' => $this->determineType($hit),
                'filesize' => $hit['bytes'],
                'width' => $hit['width'],
                'height' => $hit['height'],
                'filename' => $hit['public_id'],
                'url' => $this->provider->buildVariation($value, 'admin', $thumbOptions)->url,
            );
        }

        return $listFormatted;
    }
}
