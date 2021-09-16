<?php

declare(strict_types=1);

namespace Netgen\RemoteMedia\Core\Provider\Cloudinary\TransformationHandler;

use Netgen\RemoteMedia\API\Values\RemoteResource;
use Netgen\RemoteMedia\Core\Transformation\HandlerInterface;
use Netgen\RemoteMedia\Exception\TransformationHandlerFailedException;
use function array_key_exists;

/**
 * Class Crop.
 *
 * If there are saved values for the crop in the database
 * for the current alias (format), it builds crop options.
 * Otherwise, fails.
 */
class Crop implements HandlerInterface
{
    /**
     * Takes options from the configuration and returns
     * properly configured array of options.
     */
    public function process(RemoteResource $resource, string $variationName, array $config = []): array
    {
        if (array_key_exists($variationName, $resource->variations)) {
            $coords = $resource->variations[$variationName];

            $options[] = [
                'x' => (int) $coords['x'],
                'y' => (int) $coords['y'],
                'width' => (int) $coords['w'],
                'height' => (int) $coords['h'],
                'crop' => 'crop',
            ];

            return $options;
        }

        throw new TransformationHandlerFailedException(self::class);
    }
}
