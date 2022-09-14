<?php

declare(strict_types=1);

namespace Netgen\Bundle\RemoteMediaBundle\Templating\Twig\Runtime;

use Netgen\RemoteMedia\API\ProviderInterface;
use Netgen\RemoteMedia\API\Values\RemoteResource;
use Netgen\RemoteMedia\API\Values\RemoteResourceLocation;
use Netgen\RemoteMedia\API\Values\RemoteResourceVariation;
use Netgen\RemoteMedia\Exception\RemoteResourceLocationNotFoundException;
use Netgen\RemoteMedia\Exception\RemoteResourceNotFoundException;
use Twig\Extension\AbstractExtension;

final class RemoteMediaRuntime extends AbstractExtension
{
    protected ProviderInterface $provider;

    public function __construct(ProviderInterface $provider)
    {
        $this->provider = $provider;
    }

    public function getRemoteResource(int $resourceId): ?RemoteResource
    {
        try {
            return $this->provider->load($resourceId);
        } catch (RemoteResourceNotFoundException $e) {
            return null;
        }
    }

    public function getRemoteResourceLocation(int $locationId): ?RemoteResourceLocation
    {
        try {
            return $this->provider->loadLocation($locationId);
        } catch (RemoteResourceLocationNotFoundException $e) {
            return null;
        }
    }

    public function getRemoteResourceByRemoteId(string $remoteId): ?RemoteResource
    {
        try {
            return $this->provider->loadByRemoteId($remoteId);
        } catch (RemoteResourceNotFoundException $e) {
            return null;
        }
    }

    public function getRemoteResourceFromRemote(string $remoteId): ?RemoteResource
    {
        try {
            return $this->provider->loadFromRemote($remoteId);
        } catch (RemoteResourceNotFoundException $e) {
            return null;
        }
    }

    public function buildRemoteResourceVariation(RemoteResourceLocation $location, string $variationGroup, string $variationName): RemoteResourceVariation
    {
        return $this->provider->buildVariation($location, $variationGroup, $variationName);
    }

    public function buildRemoteResourceRawVariation(RemoteResource $resource, array $transformations): RemoteResourceVariation
    {
        return $this->provider->buildRawVariation($resource, $transformations);
    }

    public function getVideoThumbnail(RemoteResource $resource, ?int $startOffset = null): RemoteResourceVariation
    {
        return $this->provider->buildVideoThumbnail($resource, $startOffset);
    }

    public function getVideoThumbnailVariation(RemoteResourceLocation $location, string $variationGroup, string $variationName, ?int $startOffset = null): RemoteResourceVariation
    {
        return $this->provider->buildVideoThumbnailVariation($location, $variationGroup, $variationName, $startOffset);
    }

    public function getVideoThumbnailRawVariation(RemoteResource $resource, array $transformations, ?int $startOffset = null): RemoteResourceVariation
    {
        return $this->provider->buildVideoThumbnailRawVariation($resource, $transformations, $startOffset);
    }

    public function getRemoteResourceHtmlTag(RemoteResource $resource, array $htmlAttributes = [], bool $forceVideo = false): string
    {
        return $this->provider->generateHtmlTag($resource, $htmlAttributes, $forceVideo);
    }

    public function getRemoteResourceVariationHtmlTag(
        RemoteResourceLocation $location,
        string $variationGroup,
        string $variationName,
        array $htmlAttributes = [],
        bool $forceVideo = false
    ): string {
        return $this->provider->generateVariationHtmlTag(
            $location,
            $variationGroup,
            $variationName,
            $htmlAttributes,
            $forceVideo,
        );
    }

    public function getRemoteResourceRawVariationHtmlTag(
        RemoteResource $resource,
        array $transformations = [],
        array $htmlAttributes = [],
        bool $forceVideo = false
    ): string {
        return $this->provider->generateRawVariationHtmlTag(
            $resource,
            $transformations,
            $htmlAttributes,
            $forceVideo,
        );
    }

    public function getRemoteResourceDownloadUrl(RemoteResource $resource): string
    {
        return $this->provider->generateDownloadLink($resource);
    }

    public function applyScalingFormat(array $variations): array
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
