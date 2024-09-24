<?php

declare(strict_types=1);

namespace Netgen\RemoteMedia\Service;

use Netgen\RemoteMedia\API\ProviderInterface;
use Netgen\RemoteMedia\API\Values\CropSettings;
use Netgen\RemoteMedia\API\Values\RemoteResource;
use Netgen\RemoteMedia\API\Values\RemoteResourceLocation;
use Netgen\RemoteMedia\Exception\RemoteResourceNotFoundException;

use function json_decode;
use function json_encode;

final class RemoteResourceService
{
    public function __construct(
        private ProviderInterface $provider,
    ) {}

    public function handleLocationUpdate(RemoteResourceLocation $remoteResourceLocation, array $data, bool $persist = false): void
    {
        $remoteResourceLocation->setSource($data['source'] ?? null);
        $remoteResourceLocation->setWatermarkText($data['watermarkText'] ?? null);
        $remoteResourceLocation->setCropSettings(
            $this->resolveCropSettings($data['cropSettings'] ?? null),
        );

        if ($persist) {
            $this->provider->storeLocation($remoteResourceLocation);
        }
    }

    public function handleRemoteUpdate(RemoteResource $remoteResource, array $data, bool $persist = false): bool
    {
        if (!$this->needsUpdateOnRemote($remoteResource, $data)) {
            return true;
        }

        $remoteResource->setAltText($data['altText'] ?? null);
        $remoteResource->setCaption($data['caption'] ?? null);
        $remoteResource->setTags($data['tags']);

        try {
            $this->provider->updateOnRemote($remoteResource);
            if ($persist) {
                $this->provider->store($remoteResource);
            }
        } catch (RemoteResourceNotFoundException $e) {
            $this->provider->remove($remoteResource);

            return false;
        }

        return true;
    }

    public function needsUpdateOnRemote(RemoteResource $remoteResource, array $data): bool
    {
        if ($remoteResource->getAltText() !== ($data['altText'] ?? null)) {
            return true;
        }

        if ($remoteResource->getCaption() !== ($data['caption'] ?? null)) {
            return true;
        }

        if ($remoteResource->getTags() !== ($data['tags'] ?? [])) {
            return true;
        }

        return false;
    }

    /**
     * @return \Netgen\RemoteMedia\API\Values\CropSettings[]
     */
    public function resolveCropSettings(?string $cropSettingsString): array
    {
        if (!$cropSettingsString) {
            return [];
        }

        $cropSettingsArray = json_decode($cropSettingsString, true);

        $cropSettings = [];
        foreach ($cropSettingsArray as $variationName => $variationCropSettings) {
            $cropSettings[] = CropSettings::fromArray($variationName, $variationCropSettings);
        }

        return $cropSettings;
    }

    public function resolveCropSettingsString(RemoteResourceLocation $location): string
    {
        return json_encode($this->resolveCropSettingsJson($location));
    }

    public function resolveCropSettingsJson(RemoteResourceLocation $location): array
    {
        $cropSettings = [];
        foreach ($location->getCropSettings() as $cropSetting) {
            $cropSettings[$cropSetting->getVariationName()] = [
                'x' => $cropSetting->getX(),
                'y' => $cropSetting->getY(),
                'w' => $cropSetting->getWidth(),
                'h' => $cropSetting->getHeight(),
            ];
        }

        return $cropSettings;
    }
}
