<?php

declare(strict_types=1);

namespace Netgen\RemoteMedia\Form\DataTransformer;

use Netgen\RemoteMedia\API\ProviderInterface;
use Netgen\RemoteMedia\API\Values\RemoteResourceLocation;
use Netgen\RemoteMedia\Exception\RemoteResourceLocationNotFoundException;
use Netgen\RemoteMedia\Exception\RemoteResourceNotFoundException;
use Netgen\RemoteMedia\Service\RemoteResourceService;
use Symfony\Component\Form\DataTransformerInterface;

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
            'altText' => $value->getRemoteResource()->getAltText(),
            'caption' => $value->getRemoteResource()->getCaption(),
            'tags' => $value->getRemoteResource()->getTags(),
            'cropSettings' => $this->service->resolveCropSettingsString($value),
            'source' => $value->getSource(),
            'watermarkText' => $value->getWatermarkText(),
        ];
    }

    public function reverseTransform($value)
    {
        if ($value['remoteId'] === null) {
            return null;
        }

        try {
            $remoteResource = $this->provider->loadByRemoteId((string) $value['remoteId']);
        } catch (RemoteResourceNotFoundException $e) {
            try {
                $remoteResource = $this->provider->loadFromRemote((string) $value['remoteId']);
            } catch (RemoteResourceNotFoundException $e) {
                return null;
            }
        }

        try {
            $remoteResourceLocation = $value['locationId'] !== null && $value['locationId'] !== ''
                ? $this->provider->loadLocation((int) $value['locationId'])
                : new RemoteResourceLocation($remoteResource);
        } catch (RemoteResourceLocationNotFoundException $e) {
            $remoteResourceLocation = new RemoteResourceLocation($remoteResource);
        }

        if ($remoteResourceLocation->getRemoteResource()->getRemoteId() !== $remoteResource->getRemoteId()) {
            $remoteResourceLocation = new RemoteResourceLocation($remoteResource);
        }

        $needsUpdateOnRemote = $this->service->needsUpdateOnRemote($remoteResource, $value);

        $remoteResource->setAltText($value['altText'] ?? null);
        $remoteResource->setCaption($value['caption'] ?? null);
        $remoteResource->setTags($value['tags']);

        if ($needsUpdateOnRemote) {
            try {
                $this->provider->updateOnRemote($remoteResource);
            } catch (RemoteResourceNotFoundException $e) {
                $this->provider->remove($remoteResource);

                return null;
            }
        }

        $remoteResourceLocation->setSource($value['source'] ?? null);
        $remoteResourceLocation->setWatermarkText($value['watermarkText'] ?? null);

        $remoteResourceLocation->setCropSettings(
            $this->service->resolveCropSettings($value),
        );

        return $remoteResourceLocation;
    }
}
