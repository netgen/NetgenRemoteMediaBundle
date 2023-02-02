<?php

declare(strict_types=1);

namespace Netgen\RemoteMedia\Form\DataTransformer;

use Netgen\RemoteMedia\API\ProviderInterface;
use Netgen\RemoteMedia\API\Values\CropSettings;
use Netgen\RemoteMedia\API\Values\RemoteResourceLocation;
use Netgen\RemoteMedia\Exception\RemoteResourceLocationNotFoundException;
use Netgen\RemoteMedia\Exception\RemoteResourceNotFoundException;
use Symfony\Component\Form\DataTransformerInterface;

use function json_decode;
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
            'tags' => $value->getRemoteResource()->getTags(),
            'cropSettings' => $this->resolveCropSettingsString($value),
        ];
    }

    public function reverseTransform($value)
    {
        try {
            $remoteResourceLocation = $value['locationId'] ? $this->provider->loadLocation((int) $value['locationId']) : null;
        } catch (RemoteResourceLocationNotFoundException $e) {
            $remoteResourceLocation = null;
        }

        if (!$value['remoteId']) {
            if ($remoteResourceLocation instanceof RemoteResourceLocation) {
                $this->provider->removeLocation($remoteResourceLocation);
            }

            return null;
        }

        try {
            $remoteResource = $this->provider->loadByRemoteId((string) $value['remoteId']);
        } catch (RemoteResourceNotFoundException $e) {
            try {
                $remoteResource = $this->provider->loadFromRemote((string) $value['remoteId']);
            } catch (RemoteResourceNotFoundException $e) {
                if ($remoteResourceLocation instanceof RemoteResourceLocation) {
                    $this->provider->removeLocation($remoteResourceLocation);
                }

                return null;
            }
        }

        $remoteResource->setAltText($value['altText'] ?? null);
        $remoteResource->setCaption($value['caption'] ?? null);
        $remoteResource->setTags($value['tags']);

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

    public function resolveCropSettingsString(RemoteResourceLocation $location): string
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

        return json_encode($cropSettings);
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
