<?php

declare(strict_types=1);


namespace Netgen\Bundle\RemoteMediaBundle\Controller\EzAdminUI;


use eZ\Publish\API\Repository\Repository;
use eZ\Publish\API\Repository\Values\Content\Field;
use Netgen\Bundle\RemoteMediaBundle\RemoteMedia\RemoteMediaProvider;
use Netgen\Bundle\RemoteMediaBundle\RemoteMedia\UploadFile;
use RuntimeException;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;

final class UploadController
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

    public function __invoke(Request $request, $contentId)
    {
        $file = $request->files->get('file');

        $fieldId = $request->get('AttributeID');
        $contentVersionId = $request->get('ContentObjectVersion');
        $folder = $request->get('folder', 'all');

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

        $options = array();
        if ($folder !== 'all') {
            $options['folder'] = $folder;
        }

        $uploadFile = UploadFile::fromUploadedFile($file);
        $value = $this->remoteMediaProvider->upload($uploadFile, $options);

        $contentUpdateStruct = $contentService->newContentUpdateStruct();
        $contentUpdateStruct->setField($modifiedField->fieldDefIdentifier, $value);

        $contentService->updateContent($content->getVersionInfo(), $contentUpdateStruct);

        return new JsonResponse([
            'media' => $value,
            'content' => $value// @todo: ugly hack
        ]);
    }
}
