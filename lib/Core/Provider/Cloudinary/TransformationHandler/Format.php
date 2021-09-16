<?php

declare(strict_types=1);

namespace Netgen\RemoteMedia\Core\Provider\Cloudinary\TransformationHandler;

use Netgen\RemoteMedia\API\Values\RemoteResource;
use Netgen\RemoteMedia\Core\Transformation\HandlerInterface;
use Netgen\RemoteMedia\Exception\TransformationHandlerFailedException;

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
class Format implements HandlerInterface
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

        return [
            'fetch_format' => $config[0],
        ];
    }
}
