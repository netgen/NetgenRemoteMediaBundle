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
use function sort;

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
        if (!is_array($value) || ($value['remoteId'] ?? null) === null || ($value['remoteId'] ?? '') === '') {
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

        $needsUpdateOnRemote = $this->service->needsUpdateOnRemote($remoteResource, $value);

        $remoteResource->setAltText($this->normalizeNullableString($value['altText'] ?? null));
        $remoteResource->setCaption($this->normalizeNullableString($value['caption'] ?? null));
        $remoteResource->setTags($this->normalizeTags($value['tags'] ?? []));

        if ($needsUpdateOnRemote) {
            try {
                $this->provider->updateOnRemote($remoteResource);
            } catch (RemoteResourceNotFoundException) {
            }
        }

        $remoteResourceLocation->setSource($value['source'] ?? null);
        $remoteResourceLocation->setWatermarkText($value['watermarkText'] ?? null);

        $remoteResourceLocation->setCropSettings(
            $this->service->resolveCropSettings($value['cropSettings'] ?? null),
        );

        return $remoteResourceLocation;
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
