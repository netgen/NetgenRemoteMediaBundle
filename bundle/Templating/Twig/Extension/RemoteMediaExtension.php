<?php

declare(strict_types=1);

namespace Netgen\Bundle\RemoteMediaBundle\Templating\Twig\Extension;

use Netgen\Bundle\RemoteMediaBundle\Templating\Twig\Runtime\RemoteMediaRuntime;
use Twig\Extension\AbstractExtension;
use Twig\TwigFilter;
use Twig\TwigFunction;

final class RemoteMediaExtension extends AbstractExtension
{
    /**
     * @return \Twig\TwigFunction[]
     */
    public function getFunctions(): array
    {
        return [
            new TwigFunction(
                'ngrm_remote_resource',
                [RemoteMediaRuntime::class, 'getRemoteResource'],
            ),
            new TwigFunction(
                'ngrm_remote_resource_location',
                [RemoteMediaRuntime::class, 'getRemoteResourceLocation'],
            ),
            new TwigFunction(
                'ngrm_remote_resource_by_remote_id',
                [RemoteMediaRuntime::class, 'getRemoteResourceByRemoteId'],
            ),
            new TwigFunction(
                'ngrm_remote_resource_from_remote',
                [RemoteMediaRuntime::class, 'getRemoteResourceFromRemote'],
            ),
            new TwigFunction(
                'ngrm_remote_resource_variation',
                [RemoteMediaRuntime::class, 'buildRemoteResourceVariation'],
            ),
            new TwigFunction(
                'ngrm_remote_resource_raw_variation',
                [RemoteMediaRuntime::class, 'buildRemoteResourceRawVariation'],
            ),
            new TwigFunction(
                'ngrm_video_thumbnail',
                [RemoteMediaRuntime::class, 'getVideoThumbnail'],
            ),
            new TwigFunction(
                'ngrm_video_thumbnail_variation',
                [RemoteMediaRuntime::class, 'getVideoThumbnailVariation'],
            ),
            new TwigFunction(
                'ngrm_video_thumbnail_raw_variation',
                [RemoteMediaRuntime::class, 'getVideoThumbnailRawVariation'],
            ),
            new TwigFunction(
                'ngrm_remote_resource_html_tag',
                [RemoteMediaRuntime::class, 'getRemoteResourceHtmlTag'],
            ),
            new TwigFunction(
                'ngrm_remote_resource_variation_html_tag',
                [RemoteMediaRuntime::class, 'getRemoteResourceVariationHtmlTag'],
            ),
            new TwigFunction(
                'ngrm_remote_resource_raw_variation_html_tag',
                [RemoteMediaRuntime::class, 'getRemoteResourceRawVariationHtmlTag'],
            ),
            new TwigFunction(
                'ngrm_remote_resource_download_url',
                [RemoteMediaRuntime::class, 'getRemoteResourceDownloadUrl'],
            ),
            new TwigFunction(
                'ngrm_available_variations',
                [RemoteMediaRuntime::class, 'getAvailableVariations'],
            ),
            new TwigFunction(
                'ngrm_available_croppable_variations',
                [RemoteMediaRuntime::class, 'getAvailableCroppableVariations'],
            ),
            new TwigFunction(
                'ngrm_authenticate_remote_resource',
                [RemoteMediaRuntime::class, 'authenticateRemoteResource'],
            ),
            new TwigFunction(
                'ngrm_named_remote_resource',
                [RemoteMediaRuntime::class, 'getNamedRemoteResource'],
            ),
            new TwigFunction(
                'ngrm_named_remote_resource_location',
                [RemoteMediaRuntime::class, 'getNamedRemoteResourceLocation'],
            ),
        ];
    }

    /**
     * @return \Twig\TwigFilter[]
     */
    public function getFilters(): array
    {
        return [
            new TwigFilter(
                'ngrm_scaling_format',
                [RemoteMediaRuntime::class, 'applyScalingFormat'],
            ),
        ];
    }
}
