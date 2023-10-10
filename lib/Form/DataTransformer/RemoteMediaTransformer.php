<?php

declare(strict_types=1);

namespace Netgen\RemoteMedia\Form\DataTransformer;

use Netgen\RemoteMedia\API\ProviderInterface;
use Netgen\RemoteMedia\API\Values\CropSettings;
use Netgen\RemoteMedia\API\Values\RemoteResource;
use Netgen\RemoteMedia\API\Values\RemoteResourceLocation;
use Netgen\RemoteMedia\Exception\RemoteResourceLocationNotFoundException;
use Netgen\RemoteMedia\Exception\RemoteResourceNotFoundException;
use Symfony\Component\Form\DataTransformerInterface;

use function json_decode;
use function json_encode;

final class RemoteMediaTransformer implements DataTransformerInterface
{
    public function __construct(
        private ProviderInterface $provider
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
            'cropSettings' => $this->resolveCropSettingsString($value),
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

        $needsUpdateOnRemote = $this->needsUpdateOnRemote($remoteResource, $value);

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
            $this->resolveCropSettings($value),
        );

        return $remoteResourceLocation;
    }

    private function resolveCropSettingsString(RemoteResourceLocation $location): string
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
    private function resolveCropSettings(array $data): array
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

    private function needsUpdateOnRemote(RemoteResource $remoteResource, array $data): bool
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
}
