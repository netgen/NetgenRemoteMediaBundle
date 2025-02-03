<?php

declare(strict_types=1);

namespace Netgen\RemoteMedia\Core\Provider\Cloudinary\TransformationHandler;

use Netgen\RemoteMedia\Core\Transformation\HandlerInterface;

use function array_intersect;

/**
 * Class Flags.
 *
 * This transformation allows you to set flags which Cloudinary supports,
 * see: https://cloudinary.com/documentation/transformation_reference#fl_flag.
 * If you provide unsupported flags, those will be ignored.
 */
final class Flags implements HandlerInterface
{
    private const SUPPORTED_FLAGS = [
        'alternate',
        'animated',
        'any_format',
        'apng',
        'attachment',
        'awebp',
        'c2pa',
        'clip',
        'clip_evenodd',
        'cutter',
        'draco',
        'force_icc',
        'force_strip',
        'getinfo',
        'group4',
        'hlsv3',
        'ignore_aspect_ratio',
        'ignore_mask_channels',
        'immutable_cache',
        'keep_attribution',
        'keep_dar',
        'keep_iptc',
        'layer_apply',
        'lossy',
        'mono',
        'no_overflow',
        'no_stream',
        'original',
        'png8 / png24 / png32',
        'preserve_transparency',
        'progressive',
        'rasterize',
        'region_relative',
        'relative',
        'replace_image',
        'sanitize',
        'splice',
        'streaming_attachment',
        'strip_profile',
        'text_disallow_overflow',
        'text_no_trim',
        'tiff8_lzw',
        'tiled',
        'truncate_ts',
        'waveform',
    ];

    /**
     * Takes list of flags from the configuration
     * and removes all those that are not supported.
     */
    public function process(array $config = []): array
    {
        $flags = array_intersect($config, self::SUPPORTED_FLAGS);

        return ['flags' => $flags];
    }
}
