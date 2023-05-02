<?php

declare(strict_types=1);

namespace Netgen\RemoteMedia\Core\Provider\Cloudinary\TransformationHandler;

use Netgen\RemoteMedia\Core\Transformation\HandlerInterface;
use Netgen\RemoteMedia\Exception\TransformationHandlerFailedException;

use function count;

/**
 * Class Opacity.
 *
 * Adjust the opacity of an image using the opacity transformation.
 * Specify a value between 0 and 100, representing the percentage of transparency,
 * where 100 means completely opaque and 0 is completely transparent.
 */
final class Opacity implements HandlerInterface
{
    /**
     * Takes options from the configuration and returns
     * properly configured array of options.
     */
    public function process(array $config = []): array
    {
        return match (true) {
            count($config) >= 1 => ['opacity' => $config[0]],
            default => throw new TransformationHandlerFailedException(self::class),
        };
    }
}
