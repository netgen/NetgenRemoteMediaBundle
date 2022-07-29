<?php

declare(strict_types=1);

namespace Netgen\RemoteMedia\Core\Provider\Cloudinary;

use Cloudinary\Api\Response;
use Netgen\RemoteMedia\Exception\Cloudinary\InvalidRemoteIdException;
use function count;
use function explode;
use function implode;

final class CloudinaryRemoteId
{
    private string $type;

    private string $resourceType;

    private string $resourceId;

    private function __construct(string $type, string $resourceType, string $resourceId)
    {
        $this->type = $type;
        $this->resourceType = $resourceType;
        $this->resourceId = $resourceId;
    }

    public static function fromCloudinaryResponse(Response $response): self
    {
        return new self(
            $response['type'] ?? 'upload',
            $response['resource_type'] ?? 'image',
            $response['public_id'],
        );
    }

    /**
     * @throws \Netgen\RemoteMedia\Exception\Cloudinary\InvalidRemoteIdException
     */
    public static function fromRemoteId(string $remoteId): self
    {
        $parts = explode('|', $remoteId);

        if (count($parts) !== 3) {
            throw new InvalidRemoteIdException($remoteId);
        }

        return new self(
            $parts[0],
            $parts[1],
            $parts[2],
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
}