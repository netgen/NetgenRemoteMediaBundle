<?php

declare(strict_types=1);

namespace Netgen\RemoteMedia\Core\Provider\Cloudinary\TransformationHandler;

use Netgen\RemoteMedia\Core\Transformation\HandlerInterface;
use Netgen\RemoteMedia\Exception\TransformationHandlerFailedException;

use function count;

/**
 * Class Effect.
 *
 * The value of the parameter includes the name of the effect and sometimes
 * an additional value that controls the behavior of the specific effect.
 * Cloudinary supports a large number of effects that can be applied to change
 * the visual appearance of delivered images.
 * List of all available effects:
 * http://cloudinary.com/documentation/image_transformations#applying_image_effects_and_filters
 */
final class Effect implements HandlerInterface
{
    /**
     * Takes options from the configuration and returns
     * properly configured array of options.
     */
    public function process(array $config = []): array
    {
        return match (true) {
            count($config) >= 2 => ['effect' => $config[0] . ':' . $config[1]],
            count($config) === 1 => ['effect' => $config[0]],
            default => throw new TransformationHandlerFailedException(self::class),
        };
    }
}
