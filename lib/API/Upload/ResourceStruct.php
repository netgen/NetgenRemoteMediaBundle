<?php

declare(strict_types=1);

namespace Netgen\RemoteMedia\API\Upload;

use Netgen\RemoteMedia\API\Values\Folder;
use Netgen\RemoteMedia\API\Values\RemoteResource;

final class ResourceStruct
{
    /**
     * @param string[] $tags
     * @param array<string, string> $context
     */
    public function __construct(
        private FileStruct $fileStruct,
        private string $resourceType = 'auto',
        private ?Folder $folder = null,
        private string $visibility = RemoteResource::VISIBILITY_PUBLIC,
        private ?string $filenameOverride = null,
        private bool $overwrite = false,
        private bool $invalidate = false,
        private ?string $altText = null,
        private ?string $caption = null,
        private array $tags = [],
        private array $context = []
    ) {
    }

    public function getFileStruct(): FileStruct
    {
        return $this->fileStruct;
    }

    public function getResourceType(): ?string
    {
        return $this->resourceType;
    }

    public function getFolder(): ?Folder
    {
        return $this->folder;
    }

    public function getFilenameOverride(): ?string
    {
        return $this->filenameOverride;
    }

    public function getFilename(): ?string
    {
        return $this->filenameOverride ?? $this->fileStruct->getOriginalFilename();
    }

    public function doOverwrite(): bool
    {
        return $this->overwrite;
    }

    public function doInvalidate(): bool
    {
        return $this->invalidate;
    }

    public function getVisibility(): string
    {
        return $this->visibility;
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

    /**
     * @return array<string, string>
     */
    public function getContext(): array
    {
        return $this->context;
    }
}
