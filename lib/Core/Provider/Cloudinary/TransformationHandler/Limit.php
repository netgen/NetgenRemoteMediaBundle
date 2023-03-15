<?php

declare(strict_types=1);

namespace Netgen\RemoteMedia\Core\Provider\Cloudinary\TransformationHandler;

use Netgen\RemoteMedia\Core\Transformation\HandlerInterface;

/**
 * Class Limit.
 *
 * Same as the fit mode but only if the original image is larger
 * than the given limit (width and height), in which case the image
 * is scaled down so that it takes up as much space as possible within
 * a bounding box defined by the given width and height parameters.
 * The original aspect ratio is retained and all of the original image
 * is visible. This mode doesn't scale up the image if your requested
 * dimensions are larger than the original image's.
 */
final class Limit implements HandlerInterface
{
    /**
     * Takes options from the configuration and returns
     * properly configured array of options.
     */
    public function process(array $config = []): array
    {
        $options = ['crop' => 'limit'];

        if (($config[0] ?? 0) !== 0) {
            $options['width'] = $config[0];
        }

        if (($config[1] ?? 0) !== 0) {
            $options['height'] = $config[1];
        }

        return $options;
    }
}
