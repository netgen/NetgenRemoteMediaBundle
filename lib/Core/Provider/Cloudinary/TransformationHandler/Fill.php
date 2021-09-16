<?php

declare(strict_types=1);

namespace Netgen\RemoteMedia\Core\Provider\Cloudinary\TransformationHandler;

use Netgen\RemoteMedia\API\Values\RemoteResource;
use Netgen\RemoteMedia\Core\Transformation\HandlerInterface;

/**
 * Class Fill.
 *
 * Create an image with the exact given width and height while
 * retaining the original aspect ratio, using only part of the
 * image that fills the given dimensions if necessary (only part
 * of the original image might be visible if the requested aspect
 * ratio is different from the original aspect ratio).
 */
class Fill implements HandlerInterface
{
    /**
     * Takes options from the configuration and returns
     * properly configured array of options.
     */
    public function process(RemoteResource $resource, string $variationName, array $config = []): array
    {
        $options = [
            'crop' => 'fill',
        ];

        if (isset($config[0]) && $config[0] !== 0) {
            $options['width'] = $config[0];
        }

        if (isset($config[1]) && $config[1] !== 0) {
            $options['height'] = $config[1];
        }

        return $options;
    }
}
