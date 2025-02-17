<?php

declare(strict_types=1);

namespace Netgen\RemoteMedia\Core\Provider\Cloudinary\Resolver;

use Netgen\RemoteMedia\API\Upload\ResourceStruct;
use Netgen\RemoteMedia\Core\Provider\Cloudinary\CloudinaryProvider;
use Netgen\RemoteMedia\Core\Provider\Cloudinary\Converter\VisibilityType as VisibilityTypeConverter;

use function in_array;
use function is_string;

final class UploadOptions
{
    public function __construct(
        private VisibilityTypeConverter $visibilityTypeConverter,
        private string $folderMode,
    ) {}

    public function resolve(ResourceStruct $resourceStruct): array
    {
        $options = [
            'use_filename' => true,
            'use_filename_as_display_name' => true,
            'unique_filename' => false,
            'filename_override' => $resourceStruct->getFilename(),
            'overwrite' => $resourceStruct->doOverwrite(),
            'invalidate' => $resourceStruct->doInvalidate() || $resourceStruct->doOverwrite(),
            'context' => $this->resolveContext($resourceStruct),
            'type' => $this->visibilityTypeConverter->toCloudinaryType($resourceStruct->getVisibility()),
            'resource_type' => $resourceStruct->getResourceType(),
            'access_mode' => $this->visibilityTypeConverter->toCloudinaryAccessMode($resourceStruct->getVisibility()),
            'access_control' => $this->visibilityTypeConverter->toCloudinaryAccessControl($resourceStruct->getVisibility()),
            'tags' => $resourceStruct->getTags(),
        ];

        if ($resourceStruct->getFolder() && $this->folderMode === CloudinaryProvider::FOLDER_MODE_DYNAMIC) {
            $options['asset_folder'] = $resourceStruct->getFolder()->getPath();
        }

        if ($resourceStruct->getFolder() && $this->folderMode === CloudinaryProvider::FOLDER_MODE_FIXED) {
            $options['folder'] = $resourceStruct->getFolder()->getPath();
        }

        return $options;
    }

    /**
     * @return array<string, string>
     */
    private function resolveContext(ResourceStruct $resourceStruct): array
    {
        $context = [
            'alt' => $resourceStruct->getAltText() ?? '',
            'caption' => $resourceStruct->getCaption() ?? '',
            'original_filename' => $resourceStruct->getFilename(),
        ];

        foreach ($resourceStruct->getContext() as $key => $value) {
            if (!is_string($key) || in_array($key, ['alt', 'caption', 'original_filename'], true)) {
                continue;
            }

            $context[$key] = $value;
        }

        return $context;
    }
}
