<?php

declare(strict_types=1);


namespace Netgen\Bundle\RemoteMediaBundle\Controller\EzAdminUI;


use Netgen\EzPlatformSiteApi\API\LoadService;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use \RuntimeException;

final class FetchController
{
    /** @var \Netgen\EzPlatformSiteApi\API\LoadService */
    private $loadService;

    /**
     * FetchController constructor.
     *
     * @param \Netgen\EzPlatformSiteApi\API\LoadService $loadService
     */
    public function __construct(LoadService $loadService)
    {
        $this->loadService = $loadService;
    }

    public function __invoke(Request $request, $contentId, $fieldId, $version)
    {
        $content = $this->loadService->loadContent($contentId, $version);

        if (!$content->hasFieldById($fieldId)) {
            throw new RuntimeException("Field with id '{$fieldId}' not found in content with id '{$contentId}', version '{$version}'");
        }

        $field = $content->getFieldById($fieldId);
        $value = $field->value;

        $responseData = array(
            'media' => !empty($value->resourceId) ? $value : false
        );

        return new JsonResponse($responseData);
    }
}
