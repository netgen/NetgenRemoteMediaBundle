<?php

declare(strict_types=1);

namespace Netgen\RemoteMedia\Tests\Form\Type\Fixtures;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Netgen\RemoteMedia\API\Values\RemoteResourceLocation;

/**
 * An entity owning an ordered collection of remote media, mapped the way an application would map it.
 */
#[ORM\Entity]
#[ORM\Table(name: 'test_gallery')]
class Gallery
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: 'integer')]
    private ?int $id = null;

    /**
     * @var Collection<int, RemoteResourceLocation>
     */
    #[ORM\ManyToMany(targetEntity: RemoteResourceLocation::class, cascade: ['persist'], orphanRemoval: true)]
    #[ORM\JoinTable(name: 'test_gallery_image')]
    #[ORM\JoinColumn(name: 'gallery_id', referencedColumnName: 'id', onDelete: 'CASCADE')]
    #[ORM\InverseJoinColumn(name: 'remote_resource_location_id', referencedColumnName: 'id', unique: true, onDelete: 'CASCADE')]
    #[ORM\OrderBy(['position' => 'ASC'])]
    private Collection $images;

    public function __construct()
    {
        $this->images = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    /**
     * @return Collection<int, RemoteResourceLocation>
     */
    public function getImages(): Collection
    {
        return $this->images;
    }

    public function addImage(RemoteResourceLocation $image): void
    {
        if (!$this->images->contains($image)) {
            $this->images->add($image);
        }
    }

    public function removeImage(RemoteResourceLocation $image): void
    {
        $this->images->removeElement($image);
    }
}
