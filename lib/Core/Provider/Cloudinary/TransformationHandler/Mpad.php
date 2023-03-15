<?php

declare(strict_types=1);

namespace Netgen\RemoteMedia\Core\Provider\Cloudinary\TransformationHandler;

use Netgen\RemoteMedia\Core\Transformation\HandlerInterface;

/**
 * Class Mpad.
 *
 * Same as the pad mode but only if the original image is smaller than
 * the given minimum (width and height), in which case the image is
 * scaled up to fill the given width and height while retaining the
 * original aspect ratio and with all of the original image visible.
 * This mode doesn't scale down the image if your requested dimensions
 * are smaller than the original image's. If the proportions of the
 * original image do not match the given width and height, padding is
 * added to the image to reach the required size.
 * You can also specify the color of the background in the case that padding is added.
 */
final class Mpad implements HandlerInterface
{
    /**
     * Takes options from the configuration and returns
     * properly configured array of options.
     */
    public function process(array $config = []): array
    {
        $options = ['crop' => 'mpad'];

        if (($config[0] ?? 0) !== 0) {
            $options['width'] = $config[0];
        }

        if (($config[1] ?? 0) !== 0) {
            $options['height'] = $config[1];
        }

        return $options;
    }
}
