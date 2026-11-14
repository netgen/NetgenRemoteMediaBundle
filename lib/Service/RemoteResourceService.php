<?php

declare(strict_types=1);

namespace Netgen\RemoteMedia\Service;

use Netgen\RemoteMedia\API\ProviderInterface;
use Netgen\RemoteMedia\API\Values\CropSettings;
use Netgen\RemoteMedia\API\Values\RemoteResource;
use Netgen\RemoteMedia\API\Values\RemoteResourceLocation;
use Netgen\RemoteMedia\Exception\RemoteResourceNotFoundException;
use Psr\Log\LoggerInterface;
use Psr\Log\NullLogger;

use function is_array;
use function is_string;
use function json_decode;
use function json_encode;
use function sort;

final class RemoteResourceService
{
    private LoggerInterface $logger;

    public function __construct(
        private ProviderInterface $provider,
        ?LoggerInterface $logger = null,
    ) {
        $this->logger = $logger ?? new NullLogger();
    }

    public function handleLocationUpdate(RemoteResourceLocation $remoteResourceLocation, array $data, bool $persist = false): void
    {
        $remoteResourceLocation->setSource($data['source'] ?? null);
        $remoteResourceLocation->setWatermarkText($data['watermarkText'] ?? null);
        $remoteResourceLocation->setAltTextOverride($data['altTextOverride'] ?? null);
        $remoteResourceLocation->setCaptionOverride($data['captionOverride'] ?? null);
        $remoteResourceLocation->setCropSettings(
            $this->resolveCropSettings($data['cropSettings'] ?? null),
        );

        if ($persist) {
            $this->provider->storeLocation($remoteResourceLocation);
        }
    }

    /**
     * Applies the alt text, caption and tags from the data to the resource and updates them on the remote.
     *
     * A resource which no longer exists on the remote is kept: the change is applied locally only and a warning is logged.
     *
     * @return bool False if the resource no longer exists on the remote, true otherwise
     */
    public function handleRemoteUpdate(RemoteResource $remoteResource, array $data, bool $persist = false): bool
    {
        if (!$this->needsUpdateOnRemote($remoteResource, $data)) {
            return true;
        }

        $remoteResource->setAltText($this->normalizeNullableString($data['altText'] ?? null));
        $remoteResource->setCaption($this->normalizeNullableString($data['caption'] ?? null));
        $remoteResource->setTags($this->normalizeTags($data['tags'] ?? []));

        return $this->updateOnRemote($remoteResource, $persist);
    }

    /**
     * Applies the tags to the resource and updates them on the remote. The alt text and caption of the resource are not touched.
     *
     * Used by the form field, where alt text and caption are edited only as overrides of a location.
     * A resource which no longer exists on the remote is kept, as in handleRemoteUpdate().
     *
     * @param array<mixed> $tags
     *
     * @return bool False if the resource no longer exists on the remote, true otherwise
     */
    public function handleRemoteTagsUpdate(RemoteResource $remoteResource, array $tags, bool $persist = false): bool
    {
        if ($this->normalizeTags($remoteResource->getTags()) === $this->normalizeTags($tags)) {
            return true;
        }

        $remoteResource->setTags($this->normalizeTags($tags));

        return $this->updateOnRemote($remoteResource, $persist);
    }

    /**
     * Logs a resource which was submitted but exists neither locally nor on the remote.
     */
    public function logMissingResource(string $remoteId): void
    {
        $this->logger->warning(
            '[NGRM] Remote resource "{remoteId}" exists neither locally nor on the remote, it was left out of the submitted value.',
            ['remoteId' => $remoteId],
        );
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

    private function updateOnRemote(RemoteResource $remoteResource, bool $persist): bool
    {
        $existsOnRemote = true;

        try {
            $this->provider->updateOnRemote($remoteResource);
        } catch (RemoteResourceNotFoundException) {
            $existsOnRemote = false;

            $this->logger->warning(
                '[NGRM] Remote resource "{remoteId}" no longer exists on the remote, its metadata was updated locally only.',
                ['remoteId' => $remoteResource->getRemoteId()],
            );
        }

        if ($persist) {
            $this->provider->store($remoteResource);
        }

        return $existsOnRemote;
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
