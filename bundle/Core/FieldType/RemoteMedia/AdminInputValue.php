<?php

declare(strict_types=1);

namespace Netgen\Bundle\RemoteMediaBundle\Core\FieldType\RemoteMedia;

use eZHTTPFile;
use eZHTTPTool;
use Netgen\Bundle\RemoteMediaBundle\RemoteMedia\UploadFile;
use Symfony\Component\HttpFoundation\File\UploadedFile;

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

    /**
     * NgRemoteMediaInput constructor.
     *
     * @param $resourceId
     * @param $tags
     * @param $altText
     * @param $variations
     * @param $newFile
     */
    public function __construct(
        string $resourceId,
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
    }

    public static function fromEzHttp(eZHTTPTool $http, $base, $attributeId): AdminInputValue
    {
        /** @var string $resourceId */
        $resourceId = $http->variable($base . '_media_id_' . $attributeId, '');
        /** @var string $alttext */
        $alttext = $http->variable($base . '_alttext_' . $attributeId, '');
        $tags = $http->variable($base . '_tags_' . $attributeId, []);
        $variations = $http->variable($base . '_image_variations_' . $attributeId, []);
        $variations = \json_decode($variations, true);

        $file = eZHTTPFile::fetch($base . '_new_file_' . $attributeId);

        if ($file instanceof eZHTTPFile) {
            $file = UploadFile::fromZHTTPFile($file);
        }

        return new AdminInputValue($resourceId, $tags, $alttext, $variations, $file);
    }

    public static function fromHash(array $hash): AdminInputValue
    {
        $tags = empty($hash['tags']) ? [] : $hash['tags'];
        $altText = empty($hash['alt_text']) ? '' : $hash['alt_text'];

        $file = $hash['new_file'];
        if ($file instanceof UploadedFile) {
            $file = UploadFile::fromUploadedFile($file);
        }

        return new AdminInputValue(
            $hash['resource_id'] ?? '',
            $tags,
            $altText,
             \json_decode($hash['image_variations'], true),
            $file
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

    /**
     * @return UploadFile|null
     */
    public function getNewFile()
    {
        return $this->newFile;
    }
}
