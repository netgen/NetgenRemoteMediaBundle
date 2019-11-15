<?php

declare(strict_types=1);

namespace Netgen\Bundle\RemoteMediaBundle\Controller\EzAdminUI;

use Netgen\Bundle\RemoteMediaBundle\RemoteMedia\Helper;
use Netgen\Bundle\RemoteMediaBundle\RemoteMedia\Provider\Cloudinary\Search\Query;
use Netgen\Bundle\RemoteMediaBundle\RemoteMedia\RemoteMediaProvider;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;

final class BrowseController
{
    /** @var \Netgen\Bundle\RemoteMediaBundle\RemoteMedia\RemoteMediaProvider */
    private $remoteMediaProvider;

    /** @var \Netgen\Bundle\RemoteMediaBundle\RemoteMedia\Helper */
    private $remoteMediaHelper;

    public function __construct(
        RemoteMediaProvider $remoteMediaProvider,
        Helper $remoteMediaHelper
    ) {
        $this->remoteMediaProvider = $remoteMediaProvider;
        $this->remoteMediaHelper = $remoteMediaHelper;
    }

    public function __invoke(Request $request)
    {
        $limit = 25;
        $userQuery = $request->get('q', '');
        $tag = $request->get('tag', '');;

        $type = $request->get('mediatype', 'image');
        $folder = $request->get('folder', 'all');
        $folder = $folder !== 'all' ? $folder : null;


        $nextCursor = $request->get('next_cursor', null);
        if ($nextCursor === 'null') {
            $nextCursor = null;
        }

        $query = new Query(
            $userQuery,
            $type,
            $limit,
            $folder,
            $tag,
            $nextCursor
        );

        $results = $this->remoteMediaProvider->searchResources($query);
        $list = $results->getResults();

        $result = [
            'hits' => $this->remoteMediaHelper->formatBrowseList($list),
            'load_more' => $results->getTotalCount() > $limit,
        ];

        return new JsonResponse($result);
    }
}
