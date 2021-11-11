<?php

declare(strict_types=1);

namespace Netgen\RemoteMedia\API\Values;

use function array_diff;
use function array_key_exists;
use function in_array;
use function property_exists;

final class RemoteResource
{
    public const TYPE_IMAGE = 'image';
    public const TYPE_VIDEO = 'video';
    public const TYPE_AUDIO = 'audio';
    public const TYPE_DOCUMENT = 'document';
    public const TYPE_OTHER = 'other';

    private ?int $id = null;
    private string $remoteId;
    private string $type;

    private string $url;
    private int $size = 0;

    private ?string $altText = null;
    private ?string $caption = null;

    /** @var string[] */
    private array $tags = [];

    /** @var array<string, mixed> */
    private array $metaData = [];

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

    public function updateAltText(?string $altText): void
    {
        $this->altText = $altText;
    }

    public function getCaption(): ?string
    {
        return $this->caption;
    }

    public function updateCaption(?string $caption): void
    {
        $this->altText = $caption;
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
    public function getMetaData(): array
    {
        return $this->metaData;
    }

    public function hasMetaDataProperty(string $name): bool
    {
        return array_key_exists($name, $this->metaData);
    }

    /**
     * @return mixed
     */
    public function getMetaDataProperty(string $name)
    {
        if (!$this->hasMetaDataProperty($name)) {
            return null;
        }

        return $this->metaData[$name];
    }
}
