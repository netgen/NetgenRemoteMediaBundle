<?php

declare(strict_types=1);

namespace Netgen\RemoteMedia\Core\Provider\Cloudinary\TransformationHandler;

use Netgen\RemoteMedia\Core\Transformation\HandlerInterface;

/**
 * Class Lpad.
 *
 * Same as the pad mode but only if the original image is larger than the
 * given limit (width and height), in which case the image is scaled down
 * to fill the given width and height while retaining the original aspect
 * ratio and with all of the original image visible. This mode doesn't scale
 * up the image if your requested dimensions are bigger than the original
 * image's. If the proportions of the original image do not match the given
 * width and height, padding is added to the image to reach the required size.
 * You can also specify the color of the background in the case that padding is added.
 */
final class Lpad implements HandlerInterface
{
    /**
     * Takes options from the configuration and returns
     * properly configured array of options.
     */
    public function process(array $config = []): array
    {
        $options = [
            'crop' => 'lpad',
        ];

        if (isset($config[0]) && $config[0] !== 0) {
            $options['width'] = $config[0];
        }

        if (isset($config[1]) && $config[1] !== 0) {
            $options['height'] = $config[1];
        }

        if (!empty($config[2])) {
            $options['background'] = $config[2];
        }

        return $options;
    }
}
