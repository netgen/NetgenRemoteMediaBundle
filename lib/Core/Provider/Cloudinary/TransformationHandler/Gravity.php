<?php

declare(strict_types=1);

namespace Netgen\RemoteMedia\Core\Provider\Cloudinary\TransformationHandler;

use Netgen\RemoteMedia\Core\Transformation\HandlerInterface;
use Netgen\RemoteMedia\Exception\TransformationHandlerFailedException;

use function count;

/**
 * Class Gravity
 * Determines which part of an asset to focus on, and thus which part of the asset to keep,
 * when any part of the asset is cropped.
 * For overlays, this setting determines where to place the overlay.
 * The gravity transformation parameter can be set to any compass_position, object, special_position or to auto.
 * The gravity transformation parameter can be set to auto in order to
 * automatically identify the most interesting regions in the asset, and include in the crop.
 * Further control of the automatic gravity selection is provided through the second parameter,
 * please check cloudinary transformation documentation for more syntax explanation and details.
 */
final class Gravity implements HandlerInterface
{
    /**
     * Takes options from the configuration and returns
     * properly configured array of options.
     */
    public function process(array $config = []): array
    {
        return match (true) {
            count($config) >= 2 && $config[0] === 'auto' => ['gravity' => $config[0] . ':' . $config[1]],
            count($config) === 1 => ['gravity' => $config[0]],
            default => throw new TransformationHandlerFailedException(self::class),
        };
    }
}
