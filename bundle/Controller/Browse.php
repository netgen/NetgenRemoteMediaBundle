<?php

declare(strict_types=1);

namespace Netgen\Bundle\RemoteMediaBundle\Controller;

use Netgen\RemoteMedia\API\ProviderInterface;
use Netgen\RemoteMedia\API\Search\Query;
use Netgen\RemoteMedia\API\Values\RemoteResource;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

use function basename;

final class Browse
{
    private ProviderInterface $provider;

    public function __construct(ProviderInterface $provider)
    {
        $this->provider = $provider;
    }

    public function __invoke(Request $request): Response
    {
        $query = new Query([
            'query' => $request->query->get('query'),
            'types' => $request->query->get('type') ? [$request->query->get('type')] : [],
            'folders' => $request->query->get('folder') !== null ? [$request->query->get('folder')] : [],
            'tags' => $request->query->get('tag') ? [$request->query->get('tag')] : [],
            'limit' => $request->query->get('limit') ? (int) $request->query->get('limit') : 25,
            'nextCursor' => $request->query->get('next_cursor'),
        ]);

        $results = $this->provider->search($query);

        $result = [
            'hits' => $this->formatResources($results->getResources()),
            'load_more' => $results->getNextCursor() !== null,
            'next_cursor' => $results->getNextCursor(),
        ];

        return new JsonResponse($result);
    }

    /**
     * @return array<string, mixed>
     */
    private function formatResource(RemoteResource $resource): array
    {
        $transformation = [
            'crop' => 'fit',
            'width' => 160,
            'height' => 120,
        ];

        $browseUrl = '';
        $previewUrl = '';
        if ($resource->getType() === RemoteResource::TYPE_IMAGE) {
            $browseUrl = $this->provider->buildRawVariation($resource, [$transformation])->getUrl();
            $previewUrl = $resource->getUrl();
        } elseif ($resource->getType() === RemoteResource::TYPE_VIDEO) {
            $browseUrl = $this->provider->buildVideoThumbnailRawVariation($resource, [$transformation])->getUrl();
            $previewUrl = $this->provider->buildVideoThumbnail($resource)->getUrl();
        }

        return [
            'remoteId' => $resource->getRemoteId(),
            'tags' => $resource->getTags(),
            'type' => $resource->getType(),
            'size' => $resource->getSize(),
            'width' => $resource->getMetadataProperty('width'),
            'height' => $resource->getMetadataProperty('height'),
            'filename' => basename($resource->getRemoteId()),
            'format' => $resource->getMetadataProperty('format'),
            'browseUrl' => $browseUrl,
            'previewUrl' => $previewUrl,
            'url' => $resource->getUrl(),
            'altText' => $resource->getAltText(),
        ];
    }

    /**
     * @param \Netgen\RemoteMedia\API\Values\RemoteResource[] $resources
     */
    private function formatResources(array $resources): array
    {
        $list = [];
        foreach ($resources as $resource) {
            $list[] = $this->formatResource($resource);
        }

        return $list;
    }
}
