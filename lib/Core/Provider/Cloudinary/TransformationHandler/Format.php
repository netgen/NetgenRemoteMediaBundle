<?php

declare(strict_types=1);

namespace Netgen\RemoteMedia\Core\Provider\Cloudinary\TransformationHandler;

use Netgen\RemoteMedia\Core\Transformation\HandlerInterface;
use Netgen\RemoteMedia\Exception\TransformationHandlerFailedException;

use function count;

/**
 * Class Format.
 *
 * Sets the in which format should the media be delivered.
 * Images can be uploaded to Cloudinary in various formats, and these images can be converted
 * to other formats for displaying.
 *
 * Automatic format selection can also be used to save bandwidth and optimize delivery time by
 * automatically delivering images as WebP to Chrome browsers or JPEG-XR to Internet Explorer
 * browsers. The best format will be delivered to the supported browser.
 * If a browser does not support either of these formats then the image is delivered in the format
 * specified by the file extension.
 */
final class Format implements HandlerInterface
{
    /**
     * Takes options from the configuration and returns
     * properly configured array of options.
     */
    public function process(array $config = []): array
    {
        return match (true) {
            count($config) > 0 => ['fetch_format' => $config[0]],
            default => throw new TransformationHandlerFailedException(self::class),
        };
    }
}
