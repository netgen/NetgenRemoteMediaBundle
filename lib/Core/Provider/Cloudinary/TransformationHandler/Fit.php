<?php

declare(strict_types=1);

namespace Netgen\RemoteMedia\Core\Provider\Cloudinary\TransformationHandler;

use Netgen\RemoteMedia\Core\Transformation\HandlerInterface;

/**
 * Class Fit.
 *
 * The image is resized so that it takes up as much space as possible
 * within a bounding box defined by the given width and height parameters.
 * The original aspect ratio is retained and all of the original image is visible.
 */
final class Fit implements HandlerInterface
{
    /**
     * Takes options from the configuration and returns
     * properly configured array of options.
     */
    public function process(array $config = []): array
    {
        $options = ['crop' => 'fit'];

        if (($config[0] ?? 0) !== 0) {
            $options['width'] = $config[0];
        }

        if (($config[1] ?? 0) !== 0) {
            $options['height'] = $config[1];
        }

        return $options;
    }
}
