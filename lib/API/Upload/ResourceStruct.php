<?php

declare(strict_types=1);

namespace Netgen\RemoteMedia\API\Upload;

use Netgen\RemoteMedia\API\Values\Folder;

final class ResourceStruct
{
    private FileStruct $fileStruct;

    private string $resourceType = 'auto';

    private ?Folder $folder;

    private ?string $filenameOverride;

    private bool $overwrite = false;

    private bool $invalidate = false;

    private ?string $altText;

    private ?string $caption;

    /**
     * @var string[]
     */
    private array $tags = [];

    public function __construct(
        FileStruct $fileStruct,
        string $resourceType = 'auto',
        ?Folder $folder = null,
        ?string $filenameOverride = null,
        bool $overwrite = false,
        bool $invalidate = false,
        ?string $altText = null,
        ?string $caption = null,
        array $tags = []
    ) {
        $this->fileStruct = $fileStruct;
        $this->resourceType = $resourceType;
        $this->folder = $folder;
        $this->filenameOverride = $filenameOverride;
        $this->overwrite = $overwrite;
        $this->invalidate = $invalidate;
        $this->altText = $altText;
        $this->caption = $caption;
        $this->tags = $tags;
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

    public function doOverwrite(): bool
    {
        return $this->overwrite;
    }

    public function doInvalidate(): bool
    {
        return $this->invalidate;
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
}
