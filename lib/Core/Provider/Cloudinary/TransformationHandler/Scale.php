<?php

declare(strict_types=1);

namespace Netgen\RemoteMedia\Core\Provider\Cloudinary\TransformationHandler;

use Netgen\RemoteMedia\API\Values\RemoteResource;
use Netgen\RemoteMedia\Core\Transformation\HandlerInterface;

/**
 * Class Scale.
 *
 * Change the size of the image exactly to the given width and
 * height without necessarily retaining the original aspect ratio:
 * all original image parts are visible but might be stretched or
 * shrunk. If only the width or height is given, then the image is
 * scaled to the new dimension while retaining the original aspect ratio
 */
final class Scale implements HandlerInterface
{
    /**
     * Takes options from the configuration and returns
     * properly configured array of options.
     */
    public function process(RemoteResource $resource, string $variationName, array $config = []): array
    {
        $options = [
            'crop' => 'scale',
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
