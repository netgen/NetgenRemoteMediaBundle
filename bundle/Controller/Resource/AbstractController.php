<?php

declare(strict_types=1);

namespace Netgen\Bundle\RemoteMediaBundle\Controller\Resource;

use Netgen\RemoteMedia\API\ProviderInterface;
use Netgen\RemoteMedia\API\Values\RemoteResource;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController as SymfonyAbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

use function basename;

abstract class AbstractController extends SymfonyAbstractController
{
    protected ProviderInterface $provider;

    public function __construct(ProviderInterface $provider)
    {
        $this->provider = $provider;
    }

    abstract public function __invoke(Request $request): Response;

    /**
     * @return array<string, mixed>
     */
    protected function formatResource(RemoteResource $resource): array
    {
        $transformation = [
            'crop' => 'fit',
            'width' => 160,
            'height' => 120,
        ];

        $browseUrl = '';
        $previewUrl = '';
        if ($resource->getType() === RemoteResource::TYPE_IMAGE) {
            $browseUrl = $this->provider->buildRawVariation($resource, [$transformation])->getUrl();
            $previewUrl = $resource->getUrl();
        } elseif ($resource->getType() === RemoteResource::TYPE_VIDEO) {
            $browseUrl = $this->provider->buildVideoThumbnailRawVariation($resource, [$transformation])->getUrl();
            $previewUrl = $this->provider->buildVideoThumbnail($resource)->getUrl();
        }

        return [
            'remoteId' => $resource->getRemoteId(),
            'folder' => $resource->getFolder()->getPath(),
            'tags' => $resource->getTags(),
            'type' => $resource->getType(),
            'size' => $resource->getSize(),
            'width' => $resource->getMetadataProperty('width'),
            'height' => $resource->getMetadataProperty('height'),
            'filename' => basename($resource->getRemoteId()),
            'format' => $resource->getMetadataProperty('format'),
            'browseUrl' => $browseUrl,
            'previewUrl' => $previewUrl,
            'url' => $resource->getUrl(),
            'altText' => $resource->getAltText(),
        ];
    }

    /**
     * @param \Netgen\RemoteMedia\API\Values\RemoteResource[] $resources
     */
    protected function formatResources(array $resources): array
    {
        $list = [];
        foreach ($resources as $resource) {
            $list[] = $this->formatResource($resource);
        }

        return $list;
    }
}
