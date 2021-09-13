<?php

declare(strict_types=1);

namespace Netgen\RemoteMedia\Core\Provider\Cloudinary\TransformationHandler;

use Netgen\RemoteMedia\Exception\TransformationHandlerFailedException;
use Netgen\RemoteMedia\API\Values\RemoteResource;
use Netgen\RemoteMedia\Core\Transformation\HandlerInterface;

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
    public function process(RemoteResource $resource, string $variationName, array $config = []): array
    {
        if (empty($config[0])) {
            throw new TransformationHandlerFailedException(self::class);
        }

        if (empty($config[1])) {
            return [
                'effect' => $config[0],
            ];
        }

        return [
            'effect' => $config[0] . ':' . $config[1],
        ];
    }
}
