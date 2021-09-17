<?php

declare(strict_types=1);

namespace Netgen\Bundle\RemoteMediaBundle\Templating\Twig\Extension;

use Netgen\Bundle\RemoteMediaBundle\Templating\Twig\Runtime\RemoteMediaRuntime;
use Twig\Extension\AbstractExtension;
use Twig\TwigFunction;

final class RemoteMediaExtension extends AbstractExtension
{
    /**
     * @return \Twig\TwigFunction[]
     */
    public function getFunctions()
    {
        return [
            new TwigFunction(
                'netgen_remote_media_remote_resource',
                [RemoteMediaRuntime::class, 'getRemoteResource'],
            ),
            new TwigFunction(
                'netgen_remote_media_download_url',
                [RemoteMediaRuntime::class, 'getDownloadUrl'],
            ),
            new TwigFunction(
                'netgen_remote_media_video_thumbnail_url',
                [RemoteMediaRuntime::class, 'getVideoThumbnailUrl'],
            ),
        ];
    }
}
