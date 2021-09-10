<?php

declare(strict_types=1);

namespace Netgen\RemoteMedia\Core\Provider\Cloudinary\TransformationHandler;

use Netgen\RemoteMedia\API\Values\RemoteResource;
use Netgen\RemoteMedia\Core\Transformation\HandlerInterface;

/**
 * Class Mfit.
 *
 * Same as the fit mode but only if the original image is smaller
 * than the given minimum (width and height), in which case the image
 * is scaled up so that it takes up as much space as possible within
 * a bounding box defined by the given width and height parameters.
 * The original aspect ratio is retained and all of the original image
 * is visible. This mode doesn't scale down the image if your requested
 * dimensions are smaller than the original image's.
 */
final class Mfit implements HandlerInterface
{
    /**
     * Takes options from the configuration and returns
     * properly configured array of options.
     *
     * @param string $variationName name of the configured image variation configuration
     *
     * @return array
     */
    public function process(RemoteResource $resource, string $variationName, array $config = []): array
    {
        $options = [
            'crop' => 'mfit',
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
