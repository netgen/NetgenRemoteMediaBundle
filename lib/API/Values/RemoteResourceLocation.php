<?php

declare(strict_types=1);

namespace Netgen\RemoteMedia\API\Values;

use Netgen\RemoteMedia\Exception\CropSettingsNotFoundException;

use function trim;

class RemoteResourceLocation
{
    use TimestampableTrait;

    public function __construct(
        private RemoteResource $remoteResource,
        private ?string $source = null,
        private array $cropSettings = [],
        private ?string $watermarkText = null,
        private ?int $id = null,
        private ?string $altTextOverride = null,
        private ?string $captionOverride = null,
    ) {
        $this->setAltTextOverride($altTextOverride);
        $this->setCaptionOverride($captionOverride);
    }

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
     * @return CropSettings[]
     */
    public function getCropSettings(): array
    {
        return $this->cropSettings;
    }

    /**
     * @param CropSettings[] $cropSettings
     */
    public function setCropSettings(array $cropSettings): self
    {
        $this->cropSettings = $cropSettings;

        return $this;
    }

    /**
     * @throws CropSettingsNotFoundException
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

    /**
     * Alt text local to this location which takes precedence over the alt text of the remote resource.
     */
    public function getAltTextOverride(): ?string
    {
        return $this->altTextOverride;
    }

    /**
     * Empty (or whitespace only) value removes the override, so the alt text of the remote resource is used.
     */
    public function setAltTextOverride(?string $altTextOverride): self
    {
        $this->altTextOverride = self::normalizeOverride($altTextOverride);

        return $this;
    }

    /**
     * Caption local to this location which takes precedence over the caption of the remote resource.
     */
    public function getCaptionOverride(): ?string
    {
        return $this->captionOverride;
    }

    /**
     * Empty (or whitespace only) value removes the override, so the caption of the remote resource is used.
     */
    public function setCaptionOverride(?string $captionOverride): self
    {
        $this->captionOverride = self::normalizeOverride($captionOverride);

        return $this;
    }

    /**
     * Alt text for this location: local override if set, otherwise the alt text of the remote resource.
     */
    public function getAltText(): ?string
    {
        return $this->altTextOverride ?? $this->remoteResource->getAltText();
    }

    /**
     * Caption for this location: local override if set, otherwise the caption of the remote resource.
     */
    public function getCaption(): ?string
    {
        return $this->captionOverride ?? $this->remoteResource->getCaption();
    }

    public function refresh(self $remoteResourceLocation): self
    {
        $this->setSource($remoteResourceLocation->getSource());
        $this->setCropSettings($remoteResourceLocation->getCropSettings());
        $this->setWatermarkText($remoteResourceLocation->getWatermarkText());
        $this->setAltTextOverride($remoteResourceLocation->getAltTextOverride());
        $this->setCaptionOverride($remoteResourceLocation->getCaptionOverride());

        return $this;
    }

    private static function normalizeOverride(?string $value): ?string
    {
        return $value === null || trim($value) === '' ? null : $value;
    }
}
