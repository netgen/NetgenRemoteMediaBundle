<?php

declare(strict_types=1);

namespace Netgen\RemoteMedia\Core\Provider\Cloudinary\TransformationHandler;

use Netgen\RemoteMedia\Core\Transformation\HandlerInterface;
use Netgen\RemoteMedia\Exception\TransformationHandlerFailedException;

use function count;

/**
 * Class Crop.
 *
 * If there are saved values for the crop in the database
 * for the current variation name, it builds crop options.
 * Otherwise, fails.
 */
final class Crop implements HandlerInterface
{
    /**
     * Takes options from the configuration and returns
     * properly configured array of options.
     */
    public function process(array $config = []): array
    {
        return match (true) {
            count($config) >= 4 => [
                'x' => $config[0],
                'y' => $config[1],
                'width' => $config[2],
                'height' => $config[3],
                'crop' => 'crop',
            ],
            default => throw new TransformationHandlerFailedException(self::class),
        };
    }
}
