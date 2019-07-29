<?php

declare(strict_types=1);


namespace Netgen\Bundle\RemoteMediaBundle\Controller\EzAdminUI;

use eZ\Publish\API\Repository\Repository;
use eZ\Publish\API\Repository\Values\Content\Field;
use Netgen\Bundle\RemoteMediaBundle\Core\FieldType\RemoteMedia\Value;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use \RuntimeException;

final class SaveController
{
    /** @var \eZ\Publish\API\Repository\Repository*/
    private $repository;

    public function __construct(Repository $repository)
    {
        $this->repository= $repository;
    }

    public function __invoke(Request $request, $contentId, $fieldId, $contentVersionId)
    {
        $value = null;

        $newVariations = $request->get('variations', []);

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

        /** @var Value $value */
        $value = $modifiedField->value;

        $variations = $newVariations + $value->variations;
        $value->variations = $variations;

        $contentUpdateStruct = $contentService->newContentUpdateStruct();
        $contentUpdateStruct->setField($modifiedField->fieldDefIdentifier, $value);

        $contentService->updateContent($content->getVersionInfo(), $contentUpdateStruct);

        return new JsonResponse();
    }
}
