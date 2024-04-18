<?php

declare(strict_types=1);

namespace Netgen\Bundle\RemoteMediaBundle\Controller\Location;

use Netgen\RemoteMedia\API\Values\AuthToken;
use Netgen\RemoteMedia\API\ProviderInterface;
use Netgen\RemoteMedia\Service\RemoteResourceService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;

final class SelectedImage extends AbstractController
{
    public function __construct(
        private ProviderInterface $provider,
        private RemoteResourceService $service,
    ) {}

    public function __invoke(int $locationId): JsonResponse
    {
        $remoteResourceLocation = $this->provider->loadLocation($locationId);
        $remoteResource = $remoteResourceLocation->getRemoteResource();

        $authenticatedLocation = $remoteResourceLocation;
        $variationGroup = 'ngrm_interface';
        $browseVariationName = 'browse';
        $previewVariationName = 'preview';

        if ($remoteResource->isProtected()) {
            $token = AuthToken::fromDuration(600);
            $authenticatedLocation =  $this->provider->authenticateRemoteResourceLocation($remoteResourceLocation, $token);
            $browseVariationName = 'browse_protected';
            $previewVariationName = 'preview_protected';
        }
        
        $browseUrl = null;
        $previewUrl = null;
        if ($remoteResource->getType() === 'image') {
            $browseUrl = $this->provider->buildVariation($authenticatedLocation, $variationGroup, $browseVariationName);
            $previewUrl = $this->provider->buildVariation($authenticatedLocation, $variationGroup, $previewVariationName);
        } else if ($remoteResource->getType() === 'video') {
            $browseUrl = $this->provider->buildVideoThumbnailVariation($authenticatedLocation, $variationGroup, $browseVariationName);
            $previewUrl = $this->provider->buildVideoThumbnailVariation($authenticatedLocation, $variationGroup, $previewVariationName);
        }

        return new JsonResponse([
            'id' => $remoteResource->getRemoteId() ?? '',
            'name' => $remoteResource->getName() ?? '',
            'type' => $remoteResource->getType() ?? '',
            'format' => $remoteResource->getMetadataProperty('format') ?? '',
            'url' => $remoteResource->getUrl(),
            'browse_url' => $browseUrl?->getUrl() ?? '',
            'previewUrl' => $previewUrl?->getUrl() ?? '',
            'alternateText' => $remoteResource->getAltText(),
            'caption' => $remoteResource->getCaption(),
            'watermarkText' => $remoteResourceLocation->getWatermarkText(),
            'tags' => $remoteResource->getTags(),
            'size' => $remoteResource->getSize(),
            'variations' => $this->service->resolveCropSettingsJson($remoteResourceLocation),
            'height' => $remoteResource->getMetadataProperty('height') ?? 0,
            'width' => $remoteResource->getMetadataProperty('width') ?? 0,
        ]);
    }
}
