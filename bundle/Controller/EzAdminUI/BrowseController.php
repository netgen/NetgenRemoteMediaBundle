<?php

declare(strict_types=1);

namespace Netgen\Bundle\RemoteMediaBundle\Controller\EzAdminUI;

use Netgen\Bundle\RemoteMediaBundle\RemoteMedia\Helper;
use Netgen\Bundle\RemoteMediaBundle\RemoteMedia\RemoteMediaProvider;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;

final class BrowseController
{
    /** @var \Netgen\Bundle\RemoteMediaBundle\RemoteMedia\RemoteMediaProvider */
    private $remoteMediaProvider;

    /** @var \Netgen\Bundle\RemoteMediaBundle\RemoteMedia\Helper */
    private $remoteMediaHelper;

    /**
     * BrowseController constructor.
     */
    public function __construct(
        RemoteMediaProvider $remoteMediaProvider,
        Helper $remoteMediaHelper
    ) {
        $this->remoteMediaProvider = $remoteMediaProvider;
        $this->remoteMediaHelper = $remoteMediaHelper;
    }

    public function __invoke(Request $request)
    {
        $limit = 26;
        $userQuery = $request->get('q', '');
        $offset = intval($request->get('offset', 0));

        $type = $request->get('mediatype', 'image');
        $folder = $request->get('folder', 'all');

        $searchType = $request->get('search_type', 'name'); // 'name' or 'tag'

        // if no query, ignore the type of the search, list everything
        if (empty($userQuery) && $folder === 'all') {
            $list = $this->remoteMediaProvider->listResources($limit, $offset, $type);
        } else {
            $query = $folder === 'all' ? $userQuery : $folder . '/' . $userQuery;

            // search by name or by tag
            if ($searchType === 'tag') {
                $list = $this->remoteMediaProvider->searchResourcesByTag($query, $limit, $offset, $type);
            } else {
                $list = $this->remoteMediaProvider->searchResources($query, $limit, $offset, $type);
            }

            // @ŧodo: this messes up load more, for now we'll limit searches only to selected folder!
            //    if ($folder === 'all') {
            //        $folders = $provider->listFolders();
            //        $queryCount = 1 + count($folders);
            //        foreach ($folders as $folder) {
            //            $query = $folder['path'] . '/' . $userQuery;
            //
            //            if ($searchType === 'tag') {
            //                $folderList = $provider->searchResourcesByTag($query, $limit, $offset, $type);
            //            } else {
            //                $folderList= $provider->searchResources($query, $limit, $offset, $type);
            //            }
            //
            //            $list = array_merge($list, $folderList);
            //        }
            //    }
        }

        $loadMore = false;
        if (\count($list) > 25) {
            \array_pop($list);
            $loadMore = true;
        }

        $result = [
            'hits' => $this->remoteMediaHelper->formatBrowseList($list),
            'load_more' => $loadMore,
        ];

        return new JsonResponse($result);
    }
}
