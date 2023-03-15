<?php

declare(strict_types=1);

namespace Netgen\RemoteMedia\Core\Provider\Cloudinary\TransformationHandler;

use Netgen\RemoteMedia\Core\Transformation\HandlerInterface;
use Netgen\RemoteMedia\Exception\TransformationHandlerFailedException;

use function count;

/**
 * Class Resize.
 *
 * To change the size of a image, use the width and height parameters
 * (w and h in URLs) to assign new values. You can resize the image
 * by using both the width and height parameters or with only one of them:
 * the other dimension is automatically updated to maintain the aspect ratio.
 */
final class Resize implements HandlerInterface
{
    /**
     * Takes options from the configuration and returns
     * properly configured array of options.
     */
    public function process(array $config = []): array
    {
        $options = [];

        if (($config[0] ?? 0) !== 0) {
            $options['width'] = $config[0];
        }

        if (($config[1] ?? 0) !== 0) {
            $options['height'] = $config[1];
        }

        return match (true) {
            count($options) > 0 => $options,
            default => throw new TransformationHandlerFailedException(self::class),
        };
    }
}
