<?php

declare(strict_types=1);

namespace Netgen\RemoteMedia\API\Values;

use Doctrine\ORM\Mapping as ORM;
use function array_diff;
use function array_key_exists;
use function in_array;
use function property_exists;

/**
 * @ORM\Entity()
 * @ORM\Table(name="ngrm_remote_resource")
 * @ORM\HasLifecycleCallbacks()
 */
class RemoteResource
{
    use TimestampableTrait;

    public const TYPE_IMAGE = 'image';
    public const TYPE_VIDEO = 'video';
    public const TYPE_AUDIO = 'audio';
    public const TYPE_DOCUMENT = 'document';
    public const TYPE_OTHER = 'other';

    public const SUPPORTED_TYPES = [
        self::TYPE_IMAGE,
        self::TYPE_VIDEO,
        self::TYPE_AUDIO,
        self::TYPE_DOCUMENT,
        self::TYPE_OTHER,
    ];

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
     * @var \Doctrine\ORM\PersistentCollection|array
     *
     * @ORM\OneToMany(targetEntity="Netgen\RemoteMedia\API\Values\RemoteResourceLocation", mappedBy="remoteResource", cascade={"persist", "remove"}, orphanRemoval=true)
     */
    public $locations;

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

    public function setRemoteId(string $remoteId): self
    {
        $this->remoteId = $remoteId;

        return $this;
    }

    public function getType(): string
    {
        return $this->type;
    }

    public function setType(string $type): self
    {
        $this->type = $type;

        return $this;
    }

    public function getUrl(): string
    {
        return $this->url;
    }

    public function setUrl(string $url): self
    {
        $this->url = $url;

        return $this;
    }

    public function getSize(): int
    {
        return $this->size;
    }

    public function setSize(int $size): self
    {
        $this->size = $size;

        return $this;
    }

    public function getAltText(): ?string
    {
        return $this->altText;
    }

    public function setAltText(?string $altText): self
    {
        $this->altText = $altText;

        return $this;
    }

    public function getCaption(): ?string
    {
        return $this->caption;
    }

    public function setCaption(?string $caption): self
    {
        $this->caption = $caption;

        return $this;
    }

    /**
     * @return string[]
     */
    public function getTags(): array
    {
        return $this->tags;
    }

    /**
     * @param string[] $tags
     */
    public function setTags(array $tags): self
    {
        $this->tags = $tags;

        return $this;
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

    /**
     * @param array<string,mixed> $metadata
     */
    public function setMetadata(array $metadata): self
    {
        $this->metadata = $metadata;

        return $this;
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
