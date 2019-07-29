<?php

declare(strict_types=1);


namespace Netgen\Bundle\RemoteMediaBundle\Controller\EzAdminUI;


use eZ\Publish\API\Repository\Repository;
use eZ\Publish\API\Repository\Values\Content\Field;
use Netgen\Bundle\RemoteMediaBundle\RemoteMedia\RemoteMediaProvider;
use RuntimeException;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;

final class ChangeController
{
    /** @var \Netgen\Bundle\RemoteMediaBundle\RemoteMedia\RemoteMediaProvider */
    private $remoteMediaProvider;

    /** @var \eZ\Publish\API\Repository\Repository */
    private $repository;

    /**
     * LoadController constructor.
     *
     * @param \Netgen\Bundle\RemoteMediaBundle\RemoteMedia\RemoteMediaProvider $remoteMediaProvider
     * @param \eZ\Publish\API\Repository\Repository $repository
     */
    public function __construct(RemoteMediaProvider $remoteMediaProvider, Repository $repository)
    {
        $this->remoteMediaProvider = $remoteMediaProvider;
        $this->repository = $repository;
    }

    public function __invoke(Request $request, $contentId, $fieldId, $contentVersionId)
    {
        $resourceId = $request->get('resource_id');

        $contentService = $this->repository->getContentService();

        /** @var \eZ\Publish\API\Repository\Values\Content\Content $content */
        $content = $this->repository->sudo(
            function (Repository $repository) use ($contentId, $contentVersionId) {
                return $repository->getContentService()->loadContent($contentId, null, $contentVersionId);
            }
        );

        $modifiedField = null;
        foreach ($content->getFields() as $field) {
            if ($field->id != $fieldId) {
                continue;
            }

            $modifiedField = $field;
        }

        if (!$modifiedField instanceof Field) {
            throw new RuntimeException('Field not found');
        }

        $updatedValue = $this->remoteMediaProvider->getRemoteResource($resourceId);
        if (empty($updatedValue->resourceId)) {
            // Cloudinary API can't search for resource by id disregarding type of the video
            $updatedValue = $this->remoteMediaProvider->getRemoteResource($resourceId, 'video');
        }

        $contentUpdateStruct = $contentService->newContentUpdateStruct();
        $contentUpdateStruct->setField($modifiedField->fieldDefIdentifier, $updatedValue);

        $contentService->updateContent($content->getVersionInfo(), $contentUpdateStruct);

        return new JsonResponse([
            'media' => $updatedValue,
            'content' => $updatedValue // @todo: ugly hack
        ]);
    }

}
