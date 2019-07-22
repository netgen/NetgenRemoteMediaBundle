<?php

namespace Netgen\Bundle\RemoteMediaBundle\Controller\EzAdminUI;


use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Netgen\Bundle\RemoteMediaBundle\RemoteMedia\RemoteMediaProvider;

final class LoadController
{
    /** @var \Netgen\Bundle\RemoteMediaBundle\RemoteMedia\RemoteMediaProvider */
    private $remoteMediaProvider;

    /**
     * LoadController constructor.
     *
     * @param \Netgen\Bundle\RemoteMediaBundle\RemoteMedia\RemoteMediaProvider $remoteMediaProvider
     */
    public function __construct(RemoteMediaProvider $remoteMediaProvider)
    {
        $this->remoteMediaProvider = $remoteMediaProvider;
    }

    public function __invoke(Request $request)
    {
        $resourceId = $request->get('resource_id');

        $value = $this->remoteMediaProvider->getRemoteResource($resourceId);

        return new JsonResponse([
            'media' => $value,
            'content' => $value // @todo: ugly hack
        ]);
    }
}
