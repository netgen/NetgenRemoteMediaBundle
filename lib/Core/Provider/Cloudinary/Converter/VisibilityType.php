<?php

declare(strict_types=1);

namespace Netgen\RemoteMedia\Core\Provider\Cloudinary\Converter;

use Netgen\RemoteMedia\API\Values\RemoteResource;

final class VisibilityType
{
    public function fromCloudinaryType(string $type): string
    {
        switch ($type) {
            case 'private':
            case 'authenticated':
                return RemoteResource::VISIBILITY_PROTECTED;

            case 'upload':
            default:
                return RemoteResource::VISIBILITY_PUBLIC;
        }
    }

    public function toCloudinaryType(string $visibility): string
    {
        switch ($visibility) {
            case RemoteResource::VISIBILITY_PROTECTED:
                return 'authenticated';

            case RemoteResource::VISIBILITY_PUBLIC:
            default:
                return 'upload';
        }
    }

    public function toCloudinaryAccessMode(string $visibility): string
    {
        switch ($visibility) {
            case RemoteResource::VISIBILITY_PROTECTED:
                return 'authenticated';

            case RemoteResource::VISIBILITY_PUBLIC:
            default:
                return 'public';
        }
    }

    public function toCloudinaryAccessControl(string $visibility): array
    {
        switch ($visibility) {
            case RemoteResource::VISIBILITY_PROTECTED:
                return [
                    ['access_type' => 'token'],
                ];

            case RemoteResource::VISIBILITY_PUBLIC:
            default:
                return [
                    ['access_type' => 'anonymous'],
                ];
        }
    }
}
