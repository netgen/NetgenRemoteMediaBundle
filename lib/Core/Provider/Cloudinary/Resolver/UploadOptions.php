<?php

declare(strict_types=1);

namespace Netgen\RemoteMedia\Core\Provider\Cloudinary\Resolver;

use Netgen\RemoteMedia\API\Upload\ResourceStruct;
use Netgen\RemoteMedia\Core\Provider\Cloudinary\CloudinaryProvider;
use Netgen\RemoteMedia\Core\Provider\Cloudinary\Converter\VisibilityType as VisibilityTypeConverter;

use function in_array;
use function is_string;
use function pathinfo;
use function preg_replace;
use function trim;

final class UploadOptions
{
    public function __construct(
        private VisibilityTypeConverter $visibilityTypeConverter,
        private string $folderMode,
        private bool $appendExtension,
        private bool $uniqueFilenames,
        private bool $appendFolderPath,
    ) {}

    public function resolve(ResourceStruct $resourceStruct): array
    {
        $filenameOverride = $resourceStruct->getFilename();

        if ($this->appendExtension === true) {
            $pathInfo = pathinfo($resourceStruct->getFilename());
            $filenameOverride = ($pathInfo['extension'] ?? null)
                ? $pathInfo['filename'] . '_' . $pathInfo['extension'] . '.' . $pathInfo['extension']
                : $pathInfo['filename'];
        }

        $options = [
            'use_filename' => true,
            'use_filename_as_display_name' => true,
            'unique_filename' => $this->uniqueFilenames,
            'filename_override' => $filenameOverride,
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

            if ($this->appendFolderPath === true) {
                $normalized = $this->normalizeFolderPath($resourceStruct->getFolder()->getPath());
                if ($normalized !== '') {
                    $options['public_id'] = $normalized . '/' . $filenameOverride;
                    unset($options['filename_override']);
                    $options['use_filename'] = false;
                }
            }
        }

        if ($resourceStruct->getFolder() && $this->folderMode === CloudinaryProvider::FOLDER_MODE_FIXED) {
            $options['folder'] = $resourceStruct->getFolder()->getPath();
        }

        return $options;
    }

    private function normalizeFolderPath(string $path): string
    {
        return trim(preg_replace('#/+#', '/', $path), '/');
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
