<?php

declare(strict_types=1);

namespace Netgen\Bundle\RemoteMediaBundle\Core\FieldType\RemoteMedia;

use eZHTTPFile;
use eZHTTPTool;
use Netgen\Bundle\RemoteMediaBundle\RemoteMedia\UploadFile;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use function json_decode;

final class AdminInputValue
{
    /** @var string */
    private $resourceId;

    /** @var array */
    private $tags = [];

    /** @var string */
    private $altText;

    /** @var array */
    private $variations = [];

    /** @var UploadFile */
    private $newFile;

    /** @var string */
    private $mediaType;

    public function __construct(
        string $resourceId,
        string $mediaType,
        array $tags = [],
        string $altText = '',
        array $variations = [],
        $newFile = null
    ) {
        $this->resourceId = $resourceId;
        $this->tags = $tags;
        $this->altText = $altText;
        $this->variations = $variations;
        $this->newFile = $newFile;
        $this->mediaType = $mediaType;
    }

    public static function fromEzHttp(eZHTTPTool $http, $base, $attributeId): AdminInputValue
    {
        /** @var string $resourceId */
        $resourceId = $http->variable($base . '_media_id_' . $attributeId, '');
        /** @var string $alttext */
        $alttext = $http->variable($base . '_alttext_' . $attributeId, '');
        $tags = $http->variable($base . '_tags_' . $attributeId, []);
        $variations = $http->variable($base . '_image_variations_' . $attributeId, '{}');
        $mediaType = $http->variable($base . '_media_type_' . $attributeId, 'image');
        $variations = json_decode($variations, true);

        $file = eZHTTPFile::fetch($base . '_new_file_' . $attributeId);

        if ($file instanceof eZHTTPFile) {
            $file = UploadFile::fromZHTTPFile($file);
        }

        return new AdminInputValue($resourceId, $mediaType, $tags, $alttext, $variations, $file);
    }

    public static function fromHash(array $hash): AdminInputValue
    {
        $tags = empty($hash['tags']) ? [] : $hash['tags'];
        $altText = empty($hash['alt_text']) ? '' : $hash['alt_text'];

        $variations = $hash['image_variations'] ?? '{}';
        $variations = json_decode($variations, true);

        $mediaType = $hash['media_type'] ?? 'image';

        $file = null;
        if ($hash['new_file'] instanceof UploadedFile) {
            $file = UploadFile::fromUploadedFile($hash['new_file']);
        }

        return new AdminInputValue(
            $hash['resource_id'] ?? '',
            $mediaType,
            $tags,
            $altText,
            $variations,
            $file,
        );
    }

    public function getResourceId(): string
    {
        return $this->resourceId;
    }

    public function getTags(): array
    {
        return $this->tags;
    }

    public function getAltText(): string
    {
        return $this->altText;
    }

    public function getVariations(): array
    {
        return $this->variations;
    }

    public function getNewFile(): ?UploadFile
    {
        return $this->newFile;
    }

    public function getMediaType(): string
    {
        return $this->mediaType;
    }
}
