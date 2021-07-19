<?php

declare(strict_types=1);

namespace Netgen\Bundle\RemoteMediaBundle\Converter\XmlText;

use DOMDocument;
use DOMXPath;
use eZ\Publish\Core\FieldType\XmlText\Converter;
use Netgen\Bundle\RemoteMediaBundle\RemoteMedia\RemoteMediaProvider;
use function basename;
use function json_decode;

class NgRemoteMediaPreConverter implements Converter
{
    const CUSTOMTAG_NAMESPACE = 'http://ez.no/namespaces/ezpublish3/custom/';

    /**
     * @var \Netgen\Bundle\RemoteMediaBundle\RemoteMedia\RemoteMediaProvider
     */
    private $remoteMediaProvider;

    public function __construct(RemoteMediaProvider $remoteMediaProvider)
    {
        $this->remoteMediaProvider = $remoteMediaProvider;
    }

    public function convert(DOMDocument $xmlDoc)
    {
        $xpath = new DOMXPath($xmlDoc);
        $tags = $xpath->query("//custom[@name='ngremotemedia']");

        /** @var \DOMElement $tag */
        foreach ($tags as $tag) {
            $src = '';
            $videoTag = '';
            $filename = '';

            $resourceId = $tag->getAttributeNS(self::CUSTOMTAG_NAMESPACE, 'resourceId');
            $resourceType = $tag->getAttributeNS(self::CUSTOMTAG_NAMESPACE, 'resourceType') !== ''
                ? $tag->getAttributeNS(self::CUSTOMTAG_NAMESPACE, 'resourceType')
                : 'image';
            $imageVariations = $tag->getAttributeNS(self::CUSTOMTAG_NAMESPACE, 'coords');
            $variation = $tag->getAttributeNS(self::CUSTOMTAG_NAMESPACE, 'variation');

            $resource = $this->remoteMediaProvider->getRemoteResource($resourceId, $resourceType);
            $resource->variations = json_decode($imageVariations, true);

            switch ($resource->resourceType) {
                case 'video':
                    $videoTag = $this->remoteMediaProvider->generateVideoTag($resource, 'embedded', $variation);
                    $src = $this->remoteMediaProvider->getVideoThumbnail($resource);

                    break;

                case 'image':
                    $src = $resource->secure_url;

                    if ($variation !== '') {
                        $variation = $this->remoteMediaProvider->buildVariation($resource, 'embedded', $variation);
                        $src = $variation->url;
                    }

                    break;

                default:
                    $filename = $resource->resourceId ?? basename($resource->resourceId);
                    $src = $resource->secure_url;
            }

            $tag->setAttributeNS(self::CUSTOMTAG_NAMESPACE, 'src', $src);
            $tag->setAttributeNS(self::CUSTOMTAG_NAMESPACE, 'videoTag', $videoTag);
            $tag->setAttributeNS(self::CUSTOMTAG_NAMESPACE, 'filename', $filename);
            $tag->setAttributeNS(self::CUSTOMTAG_NAMESPACE, 'alt', $resource->metaData['alt_text'] ?? '');
        }
    }
}
