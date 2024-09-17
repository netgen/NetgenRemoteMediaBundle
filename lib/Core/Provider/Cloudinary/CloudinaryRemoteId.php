<?php

declare(strict_types=1);

namespace Netgen\RemoteMedia\Core\Provider\Cloudinary;

use Netgen\RemoteMedia\API\Values\Folder;
use Netgen\RemoteMedia\Exception\Cloudinary\InvalidRemoteIdException;
use Netgen\RemoteMedia\Exception\NotSupportedException;

use function array_pop;
use function count;
use function explode;
use function implode;
use function sprintf;

final class CloudinaryRemoteId
{
    public function __construct(
        private string $type,
        private string $resourceType,
        private string $resourceId,
        private string $folderMode = CloudinaryProvider::FOLDER_MODE_FIXED,
    ) {}

    public static function fromCloudinaryData(array $data, string $folderMode = CloudinaryProvider::FOLDER_MODE_FIXED): self
    {
        return new self(
            $data['type'] ?? 'upload',
            $data['resource_type'] ?? 'image',
            $data['public_id'],
            $folderMode,
        );
    }

    /**
     * @throws InvalidRemoteIdException
     */
    public static function fromRemoteId(string $remoteId, string $folderMode = CloudinaryProvider::FOLDER_MODE_FIXED): self
    {
        $parts = explode('|', $remoteId);

        if (count($parts) !== 3) {
            throw new InvalidRemoteIdException($remoteId);
        }

        return new self(
            $parts[0],
            $parts[1],
            $parts[2],
            $folderMode,
        );
    }

    public function getRemoteId(): string
    {
        $parts = [
            $this->type,
            $this->resourceType,
            $this->resourceId,
        ];

        return implode('|', $parts);
    }

    public function getType(): string
    {
        return $this->type;
    }

    public function getResourceType(): string
    {
        return $this->resourceType;
    }

    public function getResourceId(): string
    {
        return $this->resourceId;
    }

    public function getFolder(): ?Folder
    {
        if ($this->folderMode !== CloudinaryProvider::FOLDER_MODE_FIXED) {
            throw new NotSupportedException(
                'Cloudinary',
                sprintf('fetching folder from path in "%s" folder mode', $this->folderMode),
            );
        }

        $resourceIdParts = explode('/', $this->resourceId);
        array_pop($resourceIdParts);

        if (count($resourceIdParts) === 0) {
            return null;
        }

        return Folder::fromPath(implode('/', $resourceIdParts));
    }
}
