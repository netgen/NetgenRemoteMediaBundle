<?php

declare(strict_types=1);

namespace Netgen\Bundle\RemoteMediaBundle\Controller\Resource;

use Netgen\RemoteMedia\API\ProviderInterface;
use Netgen\RemoteMedia\API\Values\RemoteResource;
use Netgen\RemoteMedia\API\Values\RemoteResourceLocation;
use Symfony\Component\HttpFoundation\InputBag;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

use function basename;
use function is_array;

abstract class AbstractController
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
        $location = new RemoteResourceLocation($resource);

        $browseUrl = '';
        $previewUrl = '';
        if ($resource->getType() === RemoteResource::TYPE_IMAGE) {
            $browseUrl = $this->provider->buildVariation($location, 'ngrm_interface', 'browse')->getUrl();
            $previewUrl = $this->provider->buildVariation($location, 'ngrm_interface', 'preview')->getUrl();
        } elseif ($resource->getType() === RemoteResource::TYPE_VIDEO) {
            $browseUrl = $this->provider->buildVideoThumbnailVariation($location, 'ngrm_interface', 'browse')->getUrl();
            $previewUrl = $this->provider->buildVideoThumbnailVariation($location, 'ngrm_interface', 'preview')->getUrl();
        }

        return [
            'remoteId' => $resource->getRemoteId(),
            'folder' => $resource->getFolder() ? $resource->getFolder()->getPath() : null,
            'tags' => $resource->getTags(),
            'type' => $resource->getType(),
            'visibility' => $resource->getVisibility(),
            'size' => $resource->getSize(),
            'width' => $resource->getMetadataProperty('width'),
            'height' => $resource->getMetadataProperty('height'),
            'filename' => basename($resource->getName()),
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

    protected function getArrayFromInputBag(InputBag $inputBag, string $key): array
    {
        if (!$inputBag->has($key)) {
            return [];
        }

        $value = $inputBag->get($key);

        return is_array($value) ? $value : [$value];
    }
}
