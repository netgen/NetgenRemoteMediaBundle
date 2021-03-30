<?php

namespace Netgen\Bundle\RemoteMediaBundle\Converter\XmlText;

use eZ\Publish\API\Repository\ContentService;
use eZ\Publish\API\Repository\ContentTypeService;
use eZ\Publish\Core\FieldType\XmlText\Converter;
use Netgen\Bundle\RemoteMediaBundle\Core\FieldType\RemoteMedia\Value;
use Netgen\Bundle\RemoteMediaBundle\RemoteMedia\RemoteMediaProvider;
use DOMDocument;
use DOMXPath;
use Symfony\Component\HttpFoundation\RequestStack;

class NgRemoteMediaPreConverter implements Converter
{
    const CUSTOMTAG_NAMESPACE = 'http://ez.no/namespaces/ezpublish3/custom/';

    /**
     * @var \Netgen\Bundle\RemoteMediaBundle\RemoteMedia\RemoteMediaProvider
     */
    private $remoteMediaProvider;

    /**
     * @var \Symfony\Component\HttpFoundation\RequestStack
     */
    private $requestStack;

    /**
     * @var \eZ\Publish\API\Repository\ContentService
     */
    private $contentService;

    /**
     * @var \eZ\Publish\API\Repository\ContentTypeService
     */
    private $contentTypeService;

    public function __construct(
        RemoteMediaProvider $remoteMediaProvider,
        RequestStack $requestStack,
        ContentService $contentService,
        ContentTypeService $contentTypeService
    ) {
        $this->remoteMediaProvider = $remoteMediaProvider;
        $this->requestStack = $requestStack;
        $this->contentService = $contentService;
        $this->contentTypeService = $contentTypeService;
    }

    public function convert(DOMDocument $xmlDoc)
    {
        $xpath = new DOMXPath($xmlDoc);
        $tags = $xpath->query("//custom[@name='ngremotemedia']");

        $contentId = $this->requestStack->getCurrentRequest()->attributes->get('contentId');
        $contentTypeIdentifier = null;
        if ($contentId !== '') {
            $content = $this->contentService->loadContent($contentId);
            $contentType = $this->contentTypeService->loadContentType($content->contentInfo->contentTypeId);
            $contentTypeIdentifier = $contentType->identifier;
        }

        /** @var \DOMElement $tag */
        foreach ($tags as $tag) {
            $resourceId = $tag->getAttributeNS(self::CUSTOMTAG_NAMESPACE, 'resourceId');
            $imageVariations = $tag->getAttributeNS(self::CUSTOMTAG_NAMESPACE, 'coords');
            $variation = $tag->getAttributeNS(self::CUSTOMTAG_NAMESPACE, 'variation');
            $resourceType = $tag->getAttributeNS(self::CUSTOMTAG_NAMESPACE, 'resourceType') !== ''
                ? $tag->getAttributeNS(self::CUSTOMTAG_NAMESPACE, 'resourceType')
                : 'image';

            $resource = $this->remoteMediaProvider->getRemoteResource($resourceId, $resourceType);
            $src = $resource->secure_url;

            if ($variation !== '' && $contentTypeIdentifier) {
                $variation = $this->remoteMediaProvider->buildVariation($resource, $contentTypeIdentifier, $variation);
                $src = $variation->url;
            }

            $tag->setAttributeNS(self::CUSTOMTAG_NAMESPACE, 'src', $src);

            if (array_key_exists('alt_text', $resource->metaData)) {
                $tag->setAttributeNS(self::CUSTOMTAG_NAMESPACE, 'alt', $resource->metaData['alt_text']);
            }
        }
    }
}