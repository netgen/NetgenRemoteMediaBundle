<?php

declare(strict_types=1);

namespace Netgen\Bundle\RemoteMediaBundle\Controller\Resource;

use Netgen\RemoteMedia\API\ProviderInterface;
use Netgen\RemoteMedia\API\Values\AuthToken;
use Netgen\RemoteMedia\API\Values\RemoteResource;
use Netgen\RemoteMedia\API\Values\RemoteResourceLocation;
use Symfony\Component\HttpFoundation\InputBag;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

use function is_array;
use function str_starts_with;

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
        return [
            'remoteId' => $resource->getRemoteId(),
            'folder' => $resource->getFolder() ? $resource->getFolder()->getPath() : null,
            'tags' => $resource->getTags(),
            'type' => $resource->getType(),
            'visibility' => $resource->getVisibility(),
            'size' => $resource->getSize(),
            'width' => $resource->getMetadataProperty('width'),
            'height' => $resource->getMetadataProperty('height'),
            'filename' => $resource->getName(),
            'originalFilename' => $resource->getOriginalFilename(),
            'format' => $resource->getMetadataProperty('format'),
            'browseUrl' => $this->resolveImageUrl($resource, 'browse'),
            'previewUrl' => $this->resolveImageUrl($resource, 'preview'),
            'url' => $resource->getUrl(),
            'altText' => $resource->getAltText(),
            'caption' => $resource->getCaption(),
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

        $value = $inputBag->all()[$key] ?? [];

        return is_array($value) ? $value : [$value];
    }

    private function resolveImageUrl(RemoteResource $resource, string $variationName): string
    {
        if ($resource->isProtected()) {
            $token = AuthToken::fromDuration(600);

            $resource = $this->provider->authenticateRemoteResource($resource, $token);
            $variationName .= '_protected';
        }

        if ($resource->getType() === RemoteResource::TYPE_IMAGE) {
            $variationName .= '_image';
        }

        $location = new RemoteResourceLocation($resource);

        return match ($resource->getType()) {
            RemoteResource::TYPE_IMAGE => $this->provider->buildVariation($location, 'ngrm_interface', $variationName)->getUrl(),
            RemoteResource::TYPE_VIDEO => str_starts_with($variationName, 'preview')
                ? $this->provider->buildVariation($location, 'ngrm_interface', $variationName)->getUrl()
                : $this->provider->buildVideoThumbnailVariation($location, 'ngrm_interface', $variationName)->getUrl(),
            default => '',
        };
    }
}
