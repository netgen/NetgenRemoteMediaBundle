<?php

declare(strict_types=1);

namespace Netgen\RemoteMedia\Service;

use Netgen\RemoteMedia\API\ProviderInterface;
use Netgen\RemoteMedia\API\Values\CropSettings;
use Netgen\RemoteMedia\API\Values\RemoteResource;
use Netgen\RemoteMedia\API\Values\RemoteResourceLocation;
use Netgen\RemoteMedia\Exception\RemoteResourceNotFoundException;

use function is_array;
use function is_string;
use function json_decode;
use function json_encode;
use function sort;

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

        $remoteResource->setAltText($this->normalizeNullableString($data['altText'] ?? null));
        $remoteResource->setCaption($this->normalizeNullableString($data['caption'] ?? null));
        $remoteResource->setTags($this->normalizeTags($data['tags'] ?? []));

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
        if ($this->normalizeNullableString($remoteResource->getAltText()) !== $this->normalizeNullableString($data['altText'] ?? null)) {
            return true;
        }

        if ($this->normalizeNullableString($remoteResource->getCaption()) !== $this->normalizeNullableString($data['caption'] ?? null)) {
            return true;
        }

        if ($this->normalizeTags($remoteResource->getTags()) !== $this->normalizeTags($data['tags'] ?? [])) {
            return true;
        }

        return false;
    }

    /**
     * @return CropSettings[]
     */
    public function resolveCropSettings(?string $cropSettingsString): array
    {
        if (!$cropSettingsString) {
            return [];
        }

        $cropSettingsArray = json_decode($cropSettingsString, true);
        if (!is_array($cropSettingsArray)) {
            return [];
        }

        $cropSettings = [];
        foreach ($cropSettingsArray as $variationName => $variationCropSettings) {
            if (!is_string($variationName) || !is_array($variationCropSettings)) {
                continue;
            }

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

    private function normalizeNullableString(mixed $value): ?string
    {
        if ($value === null || $value === '') {
            return null;
        }

        return (string) $value;
    }

    /**
     * @return string[]
     */
    private function normalizeTags(mixed $tags): array
    {
        if (!is_array($tags)) {
            return [];
        }

        sort($tags);

        return $tags;
    }
}
