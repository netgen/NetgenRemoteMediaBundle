<?php

declare(strict_types=1);

namespace Netgen\RemoteMedia\Form\DataTransformer;

use Netgen\RemoteMedia\API\ProviderInterface;
use Netgen\RemoteMedia\API\Values\CropSettings;
use Netgen\RemoteMedia\API\Values\RemoteResourceLocation;
use Netgen\RemoteMedia\Exception\RemoteResourceNotFoundException;
use Symfony\Component\Form\DataTransformerInterface;

use function explode;
use function implode;
use function json_encode;

final class RemoteMediaTransformer implements DataTransformerInterface
{
    private ProviderInterface $provider;

    public function __construct(ProviderInterface $provider)
    {
        $this->provider = $provider;
    }

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
            'tags' => implode(',', $value->getRemoteResource()->getTags()),
            'cropSettings' => json_encode($value->getCropSettings()),
        ];
    }

    public function reverseTransform($value)
    {
        $remoteResourceLocation = $value['locationId'] ? $this->provider->loadLocation((int) $value['locationId']) : null;

        if (!$value['remoteId']) {
            if ($remoteResourceLocation instanceof RemoteResourceLocation) {
                $this->provider->removeLocation($remoteResourceLocation);
            }

            return null;
        }

        try {
            $remoteResource = $this->provider->loadByRemoteId((string) $value['remoteId']);
        } catch (RemoteResourceNotFoundException $e) {
            $remoteResource = $this->provider->loadFromRemote((string) $value['remoteId']);
        }

        $remoteResource->setAltText($value['altText'] ?? null);
        $remoteResource->setCaption($value['caption'] ?? null);
        $remoteResource->setTags(explode(',', $value['tags'] ?? ''));

        if (!$remoteResourceLocation instanceof RemoteResourceLocation) {
            $remoteResourceLocation = new RemoteResourceLocation($remoteResource);
        }

        if ($remoteResourceLocation->getRemoteResource()->getRemoteId() !== $remoteResource->getRemoteId()) {
            $this->provider->removeLocation($remoteResourceLocation);

            $remoteResourceLocation = new RemoteResourceLocation($remoteResource);
        }

        $remoteResourceLocation->setCropSettings(
            $this->resolveCropSettings($value),
        );

        $this->provider->store($remoteResource);
        $this->provider->storeLocation($remoteResourceLocation);

        return $remoteResourceLocation;
    }

    /**
     * @return \Netgen\RemoteMedia\API\Values\CropSettings[]
     */
    public function resolveCropSettings(array $data): array
    {
        $cropSettingsString = $data['cropSettings'] ?? null;

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
}
