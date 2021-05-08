<?php

declare(strict_types=1);

namespace Netgen\Bundle\RemoteMediaBundle\Controller\EzAdminUI\Editor;

use Netgen\Bundle\RemoteMediaBundle\RemoteMedia\Helper;
use Netgen\Bundle\RemoteMediaBundle\RemoteMedia\RemoteMediaProvider;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;

final class FetchController
{
    /**
     * @var \Netgen\Bundle\RemoteMediaBundle\RemoteMedia\RemoteMediaProvider
     */
    private $remoteMediaProvider;

    /**
     * @var \Netgen\Bundle\RemoteMediaBundle\RemoteMedia\Helper
     */
    private $remoteMediaHelper;

    public function __construct(RemoteMediaProvider $remoteMediaProvider, Helper $remoteMediaHelper)
    {
        $this->remoteMediaProvider = $remoteMediaProvider;
        $this->remoteMediaHelper = $remoteMediaHelper;
    }

    public function __invoke(Request $request): Response
    {
        if (!$request->query->has('resource_id') && !$request->query->has('resource_type')) {
            throw new BadRequestHttpException('Request has to contain parameters "resource_id" and "resource_type"');
        }

        $resourceId = $request->query->get('resource_id');
        $resourceType = $request->query->get('resource_type');

        $resource = $this->remoteMediaProvider->getRemoteResource($resourceId, $resourceType);

        return new JsonResponse($this->remoteMediaHelper->formatBrowseItem($resource));
    }
}
