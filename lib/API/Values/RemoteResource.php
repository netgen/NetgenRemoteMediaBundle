<?php

declare(strict_types=1);

namespace Netgen\RemoteMedia\API\Values;

use Doctrine\ORM\Mapping as ORM;
use Doctrine\ORM\PersistentCollection;
use function array_diff;
use function array_key_exists;
use function in_array;
use function property_exists;

/**
 * @ORM\Entity()
 * @ORM\Table(name="ngrm_remote_resource")
 * @ORM\HasLifecycleCallbacks()
 */
final class RemoteResource
{
    use TimestampableTrait;

    public const TYPE_IMAGE = 'image';
    public const TYPE_VIDEO = 'video';
    public const TYPE_AUDIO = 'audio';
    public const TYPE_DOCUMENT = 'document';
    public const TYPE_OTHER = 'other';

    /**
     * @ORM\Id()
     * @ORM\GeneratedValue()
     * @ORM\Column(type="integer")
     */
    private ?int $id = null;

    /**
     * @ORM\Column(name="remote_id", unique=true, type="string", length=255)
     */
    private string $remoteId;

    /**
     * @ORM\Column(type="string", length=100)
     */
    private string $type;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private string $url;

    /**
     * @ORM\Column(type="integer")
     */
    private int $size = 0;

    /**
     * @ORM\Column(name="alt_text", type="text", length=1000, nullable=true)
     */
    private ?string $altText = null;

    /**
     * @ORM\Column(type="text", length=1000, nullable=true)
     */
    private ?string $caption = null;

    /**
     * @var string[]
     *
     * @ORM\Column(type="array", nullable=true)
     */
    private array $tags = [];

    /**
     * @var array<string, mixed>
     *
     * @ORM\Column(name="metadata", type="array", nullable=true)
     */
    private array $metadata = [];

    /**
     * @var \Doctrine\ORM\PersistentCollection
     *
     * @ORM\OneToMany(targetEntity="Netgen\RemoteMedia\API\Values\RemoteResourceLocation", mappedBy="remote_resource_id", cascade={"persist", "remove"}, orphanRemoval=true)
     */
    public PersistentCollection $locations;

    /**
     * @param array<string,mixed> $properties
     */
    public function __construct(array $properties = [])
    {
        foreach ($properties as $name => $value) {
            if (!property_exists($this, $name)) {
                continue;
            }

            $this->{$name} = $value;
        }
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getRemoteId(): string
    {
        return $this->remoteId;
    }

    public function getType(): string
    {
        return $this->type;
    }

    public function getUrl(): string
    {
        return $this->url;
    }

    public function getSize(): int
    {
        return $this->size;
    }

    public function getAltText(): ?string
    {
        return $this->altText;
    }

    public function getCaption(): ?string
    {
        return $this->caption;
    }

    /**
     * @return string[]
     */
    public function getTags(): array
    {
        return $this->tags;
    }


    public function hasTag(string $tag): bool
    {
        return in_array($tag, $this->tags, true);
    }

    public function addTag(string $tag): void
    {
        $this->tags[] = $tag;
    }

    public function removeTag(string $tag): void
    {
        if (!in_array($tag, $this->tags, true)) {
            return;
        }

        $this->tags = array_diff($this->tags, [$tag]);
    }

    /**
     * @return array<string,mixed>
     */
    public function getMetadata(): array
    {
        return $this->metadata;
    }


    public function hasMetadataProperty(string $name): bool
    {
        return array_key_exists($name, $this->metadata);
    }

    /**
     * @return mixed
     */
    public function getMetadataProperty(string $name)
    {
        if (!$this->hasMetadataProperty($name)) {
            return null;
        }

        return $this->metadata[$name];
    }
}
