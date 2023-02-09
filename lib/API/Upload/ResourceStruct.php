<?php

declare(strict_types=1);

namespace Netgen\RemoteMedia\API\Upload;

use Netgen\RemoteMedia\API\Values\Folder;
use Netgen\RemoteMedia\API\Values\RemoteResource;

use function basename;

final class ResourceStruct
{
    private FileStruct $fileStruct;

    private string $resourceType = 'auto';

    private ?Folder $folder;

    private string $visibility = RemoteResource::VISIBILITY_PUBLIC;

    private ?string $filenameOverride;

    private bool $overwrite = false;

    private bool $invalidate = false;

    private ?string $altText;

    private ?string $caption;

    /**
     * @var string[]
     */
    private array $tags = [];

    /**
     * @var array<string, string>
     */
    private array $context = [];

    public function __construct(
        FileStruct $fileStruct,
        string $resourceType = 'auto',
        ?Folder $folder = null,
        string $visibility = RemoteResource::VISIBILITY_PUBLIC,
        ?string $filenameOverride = null,
        bool $overwrite = false,
        bool $invalidate = false,
        ?string $altText = null,
        ?string $caption = null,
        array $tags = [],
        array $context = []
    ) {
        $this->fileStruct = $fileStruct;
        $this->resourceType = $resourceType;
        $this->folder = $folder;
        $this->visibility = $visibility;
        $this->filenameOverride = $filenameOverride;
        $this->overwrite = $overwrite;
        $this->invalidate = $invalidate;
        $this->altText = $altText;
        $this->caption = $caption;
        $this->tags = $tags;
        $this->context = $context;
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
        return $this->filenameOverride ?: basename($this->fileStruct->getUri());
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
