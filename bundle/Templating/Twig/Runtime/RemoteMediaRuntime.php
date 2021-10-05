<?php

declare(strict_types=1);

namespace Netgen\Bundle\RemoteMediaBundle\Templating\Twig\Runtime;

use Netgen\RemoteMedia\API\Values\RemoteResource;
use Netgen\RemoteMedia\Core\RemoteMediaProvider;
use Netgen\RemoteMedia\Core\VariationResolver;
use Netgen\RemoteMedia\Exception\RemoteResourceNotFoundException;
use Twig\Extension\AbstractExtension;

final class RemoteMediaRuntime extends AbstractExtension
{
    protected RemoteMediaProvider $provider;

    protected VariationResolver $variationResolver;

    public function __construct(RemoteMediaProvider $provider, VariationResolver $variationResolver)
    {
        $this->provider = $provider;
        $this->variationResolver = $variationResolver;
    }

    public function getRemoteResource(string $resourceId, string $resourceType): ?RemoteResource
    {
        try {
            return $this->provider->getRemoteResource($resourceId, $resourceType);
        } catch (RemoteResourceNotFoundException $e) {
            return null;
        }
    }

    public function getDownloadUrl(RemoteResource $resource): string
    {
        return $this->provider->generateDownloadLink($resource);
    }

    public function getVideoThumbnailUrl(RemoteResource $resource): string
    {
        return $this->provider->getVideoThumbnail($resource);
    }

    public function getAvailableVariations(string $variationGroup, bool $croppable = false): array
    {
        return $croppable
            ? $this->variationResolver->getCroppbableVariations($variationGroup)
            : $this->variationResolver->getVariationsForGroup($variationGroup);
    }

    public function applyScallingFormat(array $variations): array
    {
        if (empty($variations)) {
            return $variations;
        }

        $availableVariations = [];

        foreach ($variations as $variationName => $variationConfig) {
            foreach ($variationConfig['transformations'] as $name => $config) {
                if ($name !== 'crop') {
                    continue;
                }

                $availableVariations[$variationName] = $config;
            }
        }

        return $availableVariations;
    }
}
