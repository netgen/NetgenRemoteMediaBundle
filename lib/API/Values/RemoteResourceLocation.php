<?php

declare(strict_types=1);

namespace Netgen\RemoteMedia\API\Values;

use Doctrine\ORM\Mapping as ORM;
use Netgen\RemoteMedia\Exception\CropSettingsNotFoundException;

/**
 * @ORM\Entity
 *
 * @ORM\Table(name="ngrm_remote_resource_location")
 *
 * @ORM\HasLifecycleCallbacks()
 */
class RemoteResourceLocation
{
    use TimestampableTrait;

    /**
     * @ORM\Id()
     *
     * @ORM\GeneratedValue()
     *
     * @ORM\Column(type="integer")
     */
    private ?int $id = null;

    /**
     * @ORM\ManyToOne(targetEntity="Netgen\RemoteMedia\API\Values\RemoteResource", inversedBy="locations")
     */
    private RemoteResource $remoteResource;

    /**
     * @ORM\Column(type="string", nullable=true)
     */
    private ?string $source = null;

    /**
     * @var \Netgen\RemoteMedia\API\Values\CropSettings[]
     *
     * @ORM\Column(name="crop_settings", type="array")
     */
    private array $cropSettings = [];

    /**
     * @ORM\Column(type="string", nullable=true)
     */
    private ?string $watermarkText = null;

    public function __construct(
        RemoteResource $remoteResource,
        ?string $source = null,
        array $cropSettings = [],
        ?string $watermarkText = null
    ) {
        $this->remoteResource = $remoteResource;
        $this->source = $source;
        $this->cropSettings = $cropSettings;
        $this->watermarkText = $watermarkText;
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
}
