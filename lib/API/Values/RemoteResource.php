<?php

declare(strict_types=1);

namespace Netgen\RemoteMedia\API\Values;

use Doctrine\ORM\Mapping as ORM;

use function array_diff;
use function array_key_exists;
use function array_unique;
use function array_values;
use function in_array;
use function property_exists;

/**
 * @ORM\Entity()
 *
 * @ORM\Table(name="ngrm_remote_resource")
 *
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

    public const VISIBILITY_PUBLIC = 'public';
    public const VISIBILITY_PRIVATE = 'private';
    public const VISIBILITY_PROTECTED = 'protected';

    public const SUPPORTED_TYPES = [
        self::TYPE_IMAGE,
        self::TYPE_VIDEO,
        self::TYPE_AUDIO,
        self::TYPE_DOCUMENT,
        self::TYPE_OTHER,
    ];

    public const SUPPORTED_VISIBILITIES = [
        self::VISIBILITY_PUBLIC,
        self::VISIBILITY_PRIVATE,
        self::VISIBILITY_PROTECTED,
    ];

    /**
     * @var \Doctrine\ORM\PersistentCollection|array
     *
     * @ORM\OneToMany(targetEntity="Netgen\RemoteMedia\API\Values\RemoteResourceLocation", mappedBy="remoteResource", cascade={"persist", "remove"}, orphanRemoval=true)
     */
    public $locations;

    /**
     * @ORM\Id()
     *
     * @ORM\GeneratedValue()
     *
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
     * @ORM\Column(type="string", length=255)
     */
    private string $name;

    /**
     * @ORM\Column(type="integer", nullable=true)
     */
    private ?string $version = null;

    /**
     * @ORM\Column(type="object", nullable=true)
     */
    private ?Folder $folder = null;

    /**
     * @ORM\Column(type="string", length=100)
     */
    private string $visibility = self::VISIBILITY_PUBLIC;

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
     * @var string
     *
     * @ORM\Column(type="text", length=64)
     */
    private string $md5;

    /**
     * @var array<string, mixed>
     *
     * @ORM\Column(name="metadata", type="array", nullable=true)
     */
    private array $metadata = [];

    /**
     * @var array<string, mixed>
     *
     * @ORM\Column(name="context", type="array", nullable=true)
     */
    private array $context = [];

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

        $this->tags = array_unique(array_values($this->tags));
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

    public function getName(): string
    {
        return $this->name;
    }

    public function setName(string $name): self
    {
        $this->name = $name;

        return $this;
    }

    public function getVersion(): ?string
    {
        return $this->version;
    }

    public function setVersion(?string $version): self
    {
        $this->version = $version;

        return $this;
    }

    public function getFolder(): ?Folder
    {
        return $this->folder;
    }

    public function setFolder(?Folder $folder): self
    {
        $this->folder = $folder;

        return $this;
    }

    public function isPublic(): bool
    {
        return $this->visibility === self::VISIBILITY_PUBLIC;
    }

    public function isPrivate(): bool
    {
        return $this->visibility === self::VISIBILITY_PRIVATE;
    }

    public function isProtected(): bool
    {
        return $this->visibility === self::VISIBILITY_PROTECTED;
    }

    public function getVisibility(): string
    {
        return $this->visibility;
    }

    public function setVisibility(string $visibility): self
    {
        $this->visibility = $visibility;

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
        $this->tags = array_unique(array_values($tags));

        return $this;
    }

    public function hasTag(string $tag): bool
    {
        return in_array($tag, $this->tags, true);
    }

    public function addTag(string $tag): void
    {
        if (!$this->hasTag($tag)) {
            $this->tags[] = $tag;
        }

        $this->tags = array_unique(array_values($this->tags));
    }

    public function removeTag(string $tag): void
    {
        if (!in_array($tag, $this->tags, true)) {
            return;
        }

        $this->tags = array_diff($this->tags, [$tag]);
    }

    public function getMd5(): string
    {
        return $this->md5;
    }

    public function setMd5(string $md5): self
    {
        $this->md5 = $md5;

        return $this;
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

    /**
     * @return array<string,mixed>
     */
    public function getContext(): array
    {
        return $this->context;
    }

    /**
     * @param array<string,mixed> $context
     */
    public function setContext(array $context): self
    {
        $this->context = $context;

        return $this;
    }

    public function hasContextProperty(string $name): bool
    {
        return array_key_exists($name, $this->context);
    }

    /**
     * @return mixed
     */
    public function getContextProperty(string $name)
    {
        if (!$this->hasContextProperty($name)) {
            return null;
        }

        return $this->context[$name];
    }

    public function addContextProperty(string $name, string $value): self
    {
        $this->context[$name] = $value;

        return $this;
    }

    public function removeContextProperty(string $name): self
    {
        unset($this->context[$name]);

        return $this;
    }
}
