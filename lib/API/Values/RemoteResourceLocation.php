<?php

declare(strict_types=1);

namespace Netgen\RemoteMedia\API\Values;

use Netgen\RemoteMedia\Exception\CropSettingsNotFoundException;

class RemoteResourceLocation
{
    use TimestampableTrait;

    public function __construct(
        private RemoteResource $remoteResource,
        private ?string $source = null,
        private array $cropSettings = [],
        private ?string $watermarkText = null,
        private ?int $id = null,
    ) {}

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getRemoteResource(): RemoteResource
    {
        return $this->remoteResource;
    }

    public function getSource(): ?string
    {
        return $this->source;
    }

    public function setSource(?string $source): self
    {
        $this->source = $source;

        return $this;
    }

    /**
     * @return \Netgen\RemoteMedia\API\Values\CropSettings[]
     */
    public function getCropSettings(): array
    {
        return $this->cropSettings;
    }

    /**
     * @param \Netgen\RemoteMedia\API\Values\CropSettings[] $cropSettings
     */
    public function setCropSettings(array $cropSettings): self
    {
        $this->cropSettings = $cropSettings;

        return $this;
    }

    /**
     * @throws \Netgen\RemoteMedia\Exception\CropSettingsNotFoundException
     */
    public function getCropSettingsForVariation(string $variationName): CropSettings
    {
        foreach ($this->cropSettings as $cropSettings) {
            if ($cropSettings->getVariationName() === $variationName) {
                return $cropSettings;
            }
        }

        throw new CropSettingsNotFoundException($variationName);
    }

    public function getWatermarkText(): ?string
    {
        return $this->watermarkText;
    }

    public function setWatermarkText(?string $waterMarkText): self
    {
        $this->watermarkText = $waterMarkText;

        return $this;
    }

    public function refresh(self $remoteResourceLocation): self
    {
        $this->remoteResource = $remoteResourceLocation->getRemoteResource();
        $this->setSource($remoteResourceLocation->getSource());
        $this->setCropSettings($remoteResourceLocation->getCropSettings());
        $this->setWatermarkText($remoteResourceLocation->getWatermarkText());
    }
}
