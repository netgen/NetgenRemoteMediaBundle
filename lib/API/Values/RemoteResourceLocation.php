<?php

declare(strict_types=1);

namespace Netgen\RemoteMedia\API\Values;

use Doctrine\ORM\Mapping as ORM;
use Netgen\RemoteMedia\Exception\CropSettingsNotFoundException;

/**
 * @ORM\Entity
 * @ORM\Table(name="ngrm_remote_resource_location")
 * @ORM\HasLifecycleCallbacks()
 */
final class RemoteResourceLocation
{
    use TimestampableTrait;

    /**
     * @ORM\Id()
     * @ORM\GeneratedValue()
     * @ORM\Column(type="integer")
     */
    private ?int $id = null;

    /**
     * @ORM\ManyToOne(targetEntity="Netgen\RemoteMedia\API\Values\RemoteResource", inversedBy="locations")
     * @ORM\JoinColumn(name="remote_resource_id", referencedColumnName="id")
     */
    private RemoteResource $remoteResource;

    /**
     * @var \Netgen\RemoteMedia\API\Values\CropSettings[]
     *
     * @ORM\Column(name="crop_settings", type="array")
     */
    private array $cropSettings = [];

    public function __construct(RemoteResource $remoteResource, array $cropSettings = [])
    {
        $this->remoteResource = $remoteResource;
        $this->cropSettings = $cropSettings;
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getRemoteResource(): RemoteResource
    {
        return $this->remoteResource;
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
}
