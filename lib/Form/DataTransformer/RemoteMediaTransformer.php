<?php

declare(strict_types=1);

namespace Netgen\RemoteMedia\Form\DataTransformer;

use Netgen\RemoteMedia\API\ProviderInterface;
use Netgen\RemoteMedia\API\Values\RemoteResourceLocation;
use Netgen\RemoteMedia\Exception\RemoteResourceLocationNotFoundException;
use Netgen\RemoteMedia\Exception\RemoteResourceNotFoundException;
use Netgen\RemoteMedia\Service\RemoteResourceService;
use Symfony\Component\Form\DataTransformerInterface;

use function is_array;

final class RemoteMediaTransformer implements DataTransformerInterface
{
    public function __construct(
        private ProviderInterface $provider,
        private RemoteResourceService $service,
    ) {}

    public function transform($value)
    {
        if (!$value instanceof RemoteResourceLocation) {
            return null;
        }

        return [
            'locationId' => $value->getId(),
            'remoteId' => $value->getRemoteResource()->getRemoteId(),
            'type' => $value->getRemoteResource()->getType(),
            'tags' => $value->getRemoteResource()->getTags(),
            'cropSettings' => $this->service->resolveCropSettingsString($value),
            'source' => $value->getSource(),
            'watermarkText' => $value->getWatermarkText(),
            'altTextOverride' => $value->getAltTextOverride(),
            'captionOverride' => $value->getCaptionOverride(),
        ];
    }

    public function reverseTransform($value)
    {
        if (!is_array($value) || ($value['remoteId'] ?? null) === null || ($value['remoteId'] ?? '') === '') {
            return null;
        }

        try {
            $remoteResource = $this->provider->loadByRemoteId((string) $value['remoteId']);
        } catch (RemoteResourceNotFoundException $e) {
            try {
                $remoteResource = $this->provider->loadFromRemote((string) $value['remoteId']);
            } catch (RemoteResourceNotFoundException $e) {
                $this->service->logMissingResource((string) $value['remoteId']);

                return null;
            }
        }

        $locationId = $value['locationId'] ?? null;

        try {
            $remoteResourceLocation = $locationId !== null && $locationId !== ''
                ? $this->provider->loadLocation((int) $locationId)
                : new RemoteResourceLocation($remoteResource);
        } catch (RemoteResourceLocationNotFoundException $e) {
            $remoteResourceLocation = new RemoteResourceLocation($remoteResource);
        }

        if ($remoteResourceLocation->getRemoteResource()->getRemoteId() !== $remoteResource->getRemoteId()) {
            $remoteResourceLocation = new RemoteResourceLocation($remoteResource);
        }

        // Only the tags are taken over. Alt text and caption of the resource are
        // never changed by the form field, whatever the payload carries.
        // A resource that is gone from the remote is kept, the service logs it.
        $tags = $value['tags'] ?? [];
        $this->service->handleRemoteTagsUpdate($remoteResource, is_array($tags) ? $tags : []);

        $remoteResourceLocation->setSource($value['source'] ?? null);
        $remoteResourceLocation->setWatermarkText($value['watermarkText'] ?? null);
        $remoteResourceLocation->setAltTextOverride($value['altTextOverride'] ?? null);
        $remoteResourceLocation->setCaptionOverride($value['captionOverride'] ?? null);

        $remoteResourceLocation->setCropSettings(
            $this->service->resolveCropSettings($value['cropSettings'] ?? null),
        );

        return $remoteResourceLocation;
    }
}
