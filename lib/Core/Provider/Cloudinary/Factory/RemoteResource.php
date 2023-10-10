<?php

declare(strict_types=1);

namespace Netgen\RemoteMedia\Core\Provider\Cloudinary\Factory;

use Netgen\RemoteMedia\API\Factory\FileHash as FileHashFactoryInterface;
use Netgen\RemoteMedia\API\Factory\RemoteResource as RemoteResourceFactoryInterface;
use Netgen\RemoteMedia\API\Values\RemoteResource as RemoteResourceValue;
use Netgen\RemoteMedia\Core\Provider\Cloudinary\CloudinaryRemoteId;
use Netgen\RemoteMedia\Core\Provider\Cloudinary\Converter\ResourceType as ResourceTypeConverter;
use Netgen\RemoteMedia\Core\Provider\Cloudinary\Converter\VisibilityType as VisibilityTypeConverter;
use Netgen\RemoteMedia\Exception\Factory\InvalidDataException;

use function array_key_exists;
use function array_merge;
use function cloudinary_url_internal;
use function in_array;
use function pathinfo;

use const PATHINFO_FILENAME;

final class RemoteResource implements RemoteResourceFactoryInterface
{
    public function __construct(
        private ResourceTypeConverter $resourceTypeConverter,
        private VisibilityTypeConverter $visibilityTypeConverter,
        private FileHashFactoryInterface $fileHashFactory
    ) {}

    public function create($data): RemoteResourceValue
    {
        $this->validateData($data);

        $cloudinaryRemoteId = CloudinaryRemoteId::fromCloudinaryData($data);

        return new RemoteResourceValue(
            remoteId: $cloudinaryRemoteId->getRemoteId(),
            type: $this->resolveResourceType($data),
            url: $this->resolveCorrectUrl($data),
            md5: $this->resolveMd5($data),
            name: pathinfo($cloudinaryRemoteId->getResourceId(), PATHINFO_FILENAME),
            version: ($data['version'] ?? null) !== null ? (string) $data['version'] : null,
            visibility: $this->resolveVisibility($data),
            folder: $cloudinaryRemoteId->getFolder(),
            size: $data['bytes'] ?? 0,
            altText: $this->resolveAltText($data),
            caption: $this->resolveCaption($data),
            tags: $data['tags'] ?? [],
            metadata: $this->resolveMetaData($data),
            context: $this->resolveContext($data),
        );
    }

    /**
     * @param mixed $data
     *
     * @throws \Netgen\RemoteMedia\Exception\Factory\InvalidDataException
     */
    private function validateData($data): void
    {
        if (($data['public_id'] ?? null) === null) {
            throw new InvalidDataException('Missing required "public_id" property!');
        }

        if (($data['secure_url'] ?? $data['url'] ?? null) !== null) {
            return;
        }

        throw new InvalidDataException('Missing required "secure_url" or "url" property!');
    }

    private function resolveCorrectUrl(array $data): string
    {
        $options = [
            'type' => $data['type'] ?? 'upload',
            'resource_type' => $data['resource_type'] ?? 'image',
            'secure' => true,
        ];

        return cloudinary_url_internal($data['public_id'], $options);
    }

    private function resolveResourceType(array $data): string
    {
        $type = $data['resource_type'] ?? RemoteResourceValue::TYPE_OTHER;
        $format = $data['format'] ?? null;

        return $this->resourceTypeConverter->fromCloudinaryData($type, $format);
    }

    private function resolveVisibility(array $data): string
    {
        $type = $data['type'] ?? 'upload';

        return $this->visibilityTypeConverter->fromCloudinaryType($type);
    }

    private function resolveAltText(array $data): ?string
    {
        if (($data['context']['custom']['alt_text'] ?? null) !== null) {
            return $data['context']['custom']['alt_text'];
        }

        return $data['context']['alt'] ?? null;
    }

    private function resolveCaption(array $data): ?string
    {
        if (($data['context']['custom']['caption'] ?? null) !== null) {
            return $data['context']['custom']['caption'];
        }

        return $data['context']['caption'] ?? null;
    }

    private function resolveMd5(array $data): string
    {
        if ($data['etag'] ?? null) {
            return $data['etag'];
        }

        $url = $data['secure_url'] ?? $data['url'];

        return $this->fileHashFactory->createHash($url);
    }

    /**
     * @return array<string, mixed>
     */
    private function resolveMetaData(array $data): array
    {
        $supportedMetaData = [
            'width',
            'height',
            'format',
            'created_at',
            'signature',
            'overwritten',
        ];

        $metadata = $data;
        foreach ($metadata as $key => $value) {
            if (!in_array($key, $supportedMetaData, true)) {
                unset($metadata[$key]);
            }
        }

        return $metadata;
    }

    /**
     * @return array<string, string>
     */
    private function resolveContext(array $data): array
    {
        $context = $data['context'] ?? [];

        if (array_key_exists('custom', $context)) {
            $context = array_merge($context, $context['custom']);

            unset($context['custom']);
        }

        unset($context['alt'], $context['caption']);

        return $context;
    }
}
