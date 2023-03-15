<?php

declare(strict_types=1);

namespace Netgen\RemoteMedia\API\Values;

use Doctrine\ORM\PersistentCollection;

use function array_diff;
use function array_key_exists;
use function array_unique;
use function array_values;
use function in_array;

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
     * @param string[] $tags
     * @param array<string,mixed> $metadata
     * @param array<string,mixed> $context
     * @param \Doctrine\ORM\PersistentCollection|\Netgen\RemoteMedia\API\Values\RemoteResourceLocation[] $locations
     */
    public function __construct(
        private string $remoteId,
        private string $type,
        private string $url,
        private string $md5,
        private ?int $id = null,
        private ?string $name = null,
        private ?string $version = null,
        private ?string $visibility = self::VISIBILITY_PUBLIC,
        private ?Folder $folder = null,
        private int $size = 0,
        private ?string $altText = null,
        private ?string $caption = null,
        private array $tags = [],
        private array $metadata = [],
        private array $context = [],
        public PersistentCollection|array $locations = []
    ) {
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

    public function getMetadataProperty(string $name): mixed
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

    public function getContextProperty(string $name): mixed
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
