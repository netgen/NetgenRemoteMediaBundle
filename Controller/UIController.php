<?php

namespace Netgen\Bundle\RemoteMediaBundle\Controller;

use eZ\Bundle\EzPublishCoreBundle\Controller;
use eZ\Publish\API\Repository\Repository;
use eZ\Publish\Core\MVC\ConfigResolverInterface;
use Netgen\Bundle\RemoteMediaBundle\Core\FieldType\RemoteMedia\Value;
use Netgen\Bundle\RemoteMediaBundle\RemoteMedia\Helper;
use Netgen\Bundle\RemoteMediaBundle\RemoteMedia\RemoteMediaProviderInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\JsonResponse;
use Cloudinary\Api\NotFound;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;
use Symfony\Component\Templating\EngineInterface;
use eZ\Publish\Core\Base\Exceptions\UnauthorizedException;
use eZ\Publish\Core\MVC\Symfony\Security\Authorization\Attribute as AuthorizationAttribute;

class UIController extends Controller
{
    /**
     * @var \Netgen\Bundle\RemoteMediaBundle\RemoteMedia\RemoteMediaProviderInterface
     */
    protected $provider;

    /**
     * @var \Netgen\Bundle\RemoteMediaBundle\RemoteMedia\Helper
     */
    protected $helper;

    /**
     * @var \Symfony\Component\Templating\EngineInterface
     */
    protected $templating;

    /**
     * @var \eZ\Publish\API\Repository\Repository
     */
    protected $repository;

    /**
     * @var mixed
     */
    protected $anonymousUserId;

    /**
     * @var int
     */
    protected $browseLimit;

    /**
     * @var \eZ\Publish\Core\MVC\ConfigResolverInterface
     */
    protected $configResolver;

    /**
     * @var \Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface
     */
    protected $authorizationChecker;

    protected $ezoeVariationList;

    protected $ezoeClassList;



    /**
     * UIController constructor.
     *
     * @param RemoteMediaProviderInterface $provider
     * @param Helper $helper
     * @param EngineInterface $templating
     * @param Repository $repository
     * @param \eZ\Publish\Core\MVC\ConfigResolverInterface $configResolver
     * @param \Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface $authorizationChecker
     */
    public function __construct(
        RemoteMediaProviderInterface $provider,
        Helper $helper,
        EngineInterface $templating,
        Repository $repository,
        ConfigResolverInterface $configResolver,
        AuthorizationCheckerInterface $authorizationChecker
    )

    {
        $this->provider = $provider;
        $this->helper = $helper;
        $this->templating = $templating;
        $this->repository = $repository;
        $this->configResolver = $configResolver;
        $this->authorizationChecker = $authorizationChecker;
    }

    public function setEzoeVariationList($variationList)
    {
        $this->ezoeVariationList = $variationList;
    }

    public function setEzoeClassList($classList)
    {
        $this->ezoeClassList = $classList;
    }

    /**
     * Dynamic settings injection
     *
     * @param mixed|null $anonymousUserId
     */
    public function setAnonymousUserId($anonymousUserId = null)
    {
        $this->anonymousUserId = $anonymousUserId;
    }

    /**
     * Dynamic settings injection
     *
     * @param mixed|null $browseLimit
     */
    public function setBrowseLimit($browseLimit = null)
    {
        $this->browseLimit = !empty($browseLimit) ? (int) $browseLimit : 1000;
    }

    protected function checkContentPermissions($contentId, $function = 'edit')
    {
        $contentInfo = $this->repository->getContentService()->loadContentInfo($contentId);

        $this->checkPermissions('content', $function, $contentInfo);
    }

    protected function checkPermissions($module, $function, $valueObject = null)
    {
        $attribute = !empty($valueObject) ?
            new AuthorizationAttribute($module, $function, array('valueObject' => $valueObject)) :
            new AuthorizationAttribute($module, $function);

        if (!$this->authorizationChecker->isGranted($attribute)) {
            throw new UnauthorizedException('ngremotemedia', 'browse');
        }
    }

    /**
     * Uploads file to remote provider and updates the field value
     *
     * @param \Symfony\Component\HttpFoundation\Request $request
     *
     * @return \Symfony\Component\HttpFoundation\JsonResponse
     */
    public function uploadFileAction(Request $request, $contentId)
    {
        $this->checkContentPermissions($contentId);
        $this->checkContentPermissions($contentId);

        $file = $request->files->get('file', '');
        $fieldId = $request->get('AttributeID', '');
        $contentVersionId = $request->get('ContentObjectVersion', '');
        $legacy = $request->request->getBoolean('legacy', false);

        $template = $legacy ?
            'file:extension/ngremotemedia/design/standard/templates/content/datatype/edit/ngremotemedia.tpl' :
            'NetgenRemoteMediaBundle:ezexceed/edit:ngremotemedia.html.twig';

        if (empty($file) || empty($fieldId) || empty($contentVersionId)) {
            return new JsonResponse(
                array(
                    'ok' => false,
                    'error_text' => 'Not all arguments where set (file, attribute Id, content version)',
                ),
                400
            );
        }

        $value = $this->helper->upload(
            $file->getRealPath(),
            pathinfo($file->getClientOriginalName(), PATHINFO_FILENAME),
            $fieldId,
            $contentVersionId
        );

        $this->helper->updateValue($value, $contentId, $fieldId, $contentVersionId);

        $content = $this->templating->render(
            $template,
            array(
                'value' => $value,
                'fieldId' => $fieldId,
                'availableFormats' => $this->helper->loadAvailableFormats($contentId, $fieldId, $contentVersionId),
                'version' => $contentVersionId,
                'contentObjectId' => $contentId,
                'ajax' => true // tells legacy templates not to load js
            )
        );

        $responseData = array(
            'media' => !empty($value->resourceId) ? $value : false,
            'content' => $content,
            'toScale' => $this->getScaling($value),
        );

        return new JsonResponse($responseData, 200);
    }

    /**
     * @param Request $request
     *
     * @return JsonResponse
     *
     */
    public function uploadFileSimpleAction(Request $request)
    {
        $this->checkPermissions('ngremotemedia', 'upload');

        $file = $request->files->get('file', '');
        $fieldId = $request->get('AttributeID', '');
        $contentVersionId = $request->get('ContentObjectVersion', '');

        if (empty($file)) {
            return new JsonResponse(
                array(
                    'ok' => false,
                    'error_text' => 'File is empty or not set',
                ),
                400
            );
        }

        $value = $this->helper->upload(
            $file->getRealPath(),
            pathinfo($file->getClientOriginalName(), PATHINFO_FILENAME),
            $fieldId,
            $contentVersionId
        );

        $responseData = array(
            'media' => !empty($value->resourceId) ? $value : false,
        );

        return new JsonResponse($responseData, 200);
    }

    protected function getScaling(Value $value)
    {
        $variations = $value->variations;

        $scaling = array();
        foreach ($variations as $name => $coords) {
            $scaling[] = array(
                'name' => $name,
                'coords' => array(
                    (int) $coords['x'],
                    (int) $coords['y'],
                    (int) $coords['x'] + (int) $coords['w'],
                    (int) $coords['y'] + (int) $coords['h'],
                ),
            );
        }

        return $scaling;
    }

    /**
     * eZExceed:
     * Fetches the field value
     *
     * @param \Symfony\Component\HttpFoundation\Request $request
     * @param mixed $fieldId
     * @param mixed $contentVersionId
     *
     * @return \Symfony\Component\HttpFoundation\JsonResponse
     */
    public function fetchAction(Request $request, $contentId, $fieldId, $contentVersionId)
    {
        $this->checkContentPermissions($contentId);

        /** @var \Netgen\Bundle\RemoteMediaBundle\Core\FieldType\RemoteMedia\Value $value */
        $value = $this->helper->loadValue($contentId, $fieldId, $contentVersionId);

        $content = $this->templating->render(
            'NetgenRemoteMediaBundle:ezexceed/edit:ngremotemedia.html.twig',
            array(
                'value' => $value,
                'fieldId' => $fieldId,
                'availableFormats' => $this->helper->loadAvailableFormats($contentId, $fieldId, $contentVersionId),
            )
        );

        $responseData = array(
            'media' => !empty($value->resourceId) ? $value : false,
            'content' => $content,
            'toScale' => $this->getScaling($value),
        );

        return new JsonResponse($responseData, 200);
    }

    /**
     * EZOE
     *
     * @param string $resourceId
     *
     * @return JsonResponse
     */
    public function fetchRemoteAction(Request $request)
    {
        $resourceId = $request->get('resourceId');

        try {
            $value = $this->helper->getValueFromRemoteResource($resourceId, 'image');
        } catch (NotFound $e) {
            return new JsonResponse(
                array(
                    'error_text' => $e->getMessage(),
                )
            );
        }

        $versions = $this->ezoeVariationList;
        $availableVersions = array();
        // @todo: move this to helper class
        if (!empty($versions) && is_array($versions)) {
            foreach ($versions as $version) {

                $format = explode(',', $version);

                if (count($format) != 2) {
                    continue;
                }

                $size = explode('x', $format[1]);
                if (count($size) != 2) {
                    continue;
                }

                /*
                 * Both dimensions can't be unbound
                 */
                if ($size[0] == 0 && $size[1] == 0) {
                    continue;
                }

                $availableVersions[] = array(
                    'name' => $format[0],
                    'size' => $size,
                );
            }
        }

        $responseData = array(
            'media' => !empty($value) ? $value: false,
            'available_versions' => $availableVersions,
            'class_list' => $this->ezoeClassList
        );

        return new JsonResponse($responseData, 200);
    }

    /**
     * eZExceed:
     * updates the coordinates on the value
     *
     * @param \Symfony\Component\HttpFoundation\Request $request
     * @param mixed $contentId
     * @param mixed $fieldId
     * @param $contentVersionId
     *
     * @return \Symfony\Component\HttpFoundation\JsonResponse
     */
    public function updateCoordinatesAction(Request $request, $contentId, $fieldId, $contentVersionId)
    {
        $this->checkContentPermissions($contentId);

        $variantName = $request->request->get('name', '');
        $crop_x = $request->request->getInt('crop_x');
        $crop_y = $request->request->getInt('crop_y');
        $crop_w = $request->request->getInt('crop_w');
        $crop_h = $request->request->getInt('crop_h');

        if (empty($variantName) || empty($crop_w) || empty($crop_h)) {
            return new JsonResponse(
                array(
                    'error_text' => 'Missing one of the arguments: variant name, crop width, crop height',
                    'content' => null,
                ),
                400
            );
        }

        $value = $this->helper->loadValue($contentId, $fieldId, $contentVersionId);

        $variationCoords = array(
            $variantName => array(
                'x' => $crop_x,
                'y' => $crop_y,
                'w' => $crop_w,
                'h' => $crop_h,
            ),
        );

        $variations = $variationCoords + $value->variations;
        $value->variations = $variations;

        $this->helper->updateValue($value, $contentId, $fieldId, $contentVersionId);

        $variation = $this->helper->getVariationFromValue(
            $value,
            $variantName,
            $this->helper->loadAvailableFormats($contentId, $fieldId, $contentVersionId)
        );

        $responseData = array(
            'name' => $variantName,
            'url' => $variation->url,
            'coords' => array(
                $crop_x,
                $crop_y,
                $crop_x + $crop_w,
                $crop_y + $crop_h,
            ),
        );

        return new JsonResponse($responseData, 200);
    }

    /**
     * EZOE - scaling
     *
     * @param Request $request
     *
     * @throws UnauthorizedException if user does not have permission for this action
     *
     * @return JsonResponse
     */
    public function generateVariationAction(Request $request)
    {
        $this->checkPermissions('ngremotemedia', 'generate');

        $resourceId = $request->request->get('resourceId', '');
        $variantName = $request->request->get('name', '');
        $crop_x = $request->request->getInt('crop_x');
        $crop_y = $request->request->getInt('crop_y');
        $crop_w = $request->request->getInt('crop_w');
        $crop_h = $request->request->getInt('crop_h');

        if (empty($resourceId) || empty($variantName) || empty($crop_w) || empty($crop_h)) {
            return new JsonResponse(
                array(
                    'error_text' => 'Missing one of the arguments: variant name, crop width, crop height',
                    'content' => null,
                ),
                400
            );
        }

        $remoteResourceValue = $this->helper->getValueFromRemoteResource($resourceId, 'image');

        $variationCoords = array(
            $variantName => array(
                'x' => $crop_x,
                'y' => $crop_y,
                'w' => $crop_w,
                'h' => $crop_h,
            ),
        );

        $variations = $variationCoords + $remoteResourceValue->variations;
        $remoteResourceValue->variations = $variations;

        $formatListInitial = $this->ezoeVariationList;
        $formatList = array();
        foreach ($formatListInitial as $format) {
            $format = explode(',', $format);

            if (count($format) != 2) {
                continue;
            }

            $formatList[$format[0]] = $format[1];
        }

        $variation = $this->helper->getVariationFromValue(
            $remoteResourceValue,
            $variantName,
            $formatList
        );

        $responseData = array(
            'name' => $variantName,
            'url' => $variation->url,
            'coords' => array(
                $crop_x,
                $crop_y,
                $crop_x + $crop_w,
                $crop_y + $crop_h,
            )
        );

        return new JsonResponse($responseData, 200);
    }

    /**
     * eZExceed/Admin:
     * Fetches the list of available images from remote provider
     *
     * @param \Symfony\Component\HttpFoundation\Request $request
     *
     * @throws UnauthorizedException if user does not have permission to browse
     *
     * @return \Symfony\Component\HttpFoundation\JsonResponse
     */
    public function browseRemoteMediaAction(Request $request)
    {
        $this->checkPermissions('ngremotemedia', 'browse');

        $limit = 25;
        $query = $request->get('q', '');
        $offset = $request->get('offset', 0);

        $list = $this->helper->searchResources($query, $offset, $limit, $this->browseLimit);

        return new JsonResponse($list, 200);
    }

    /**
     * Adds a tag to the remote resource and saves the updated field
     *
     * @param \Symfony\Component\HttpFoundation\Request $request
     * @param mixed $fieldId
     * @param mixed $contentVersionId
     *
     * @return \Symfony\Component\HttpFoundation\JsonResponse
     */
    public function addTagsAction(Request $request, $contentId, $fieldId, $contentVersionId)
    {
        $this->checkContentPermissions($contentId);

        $resourceId = $request->get('id', '');
        $tag = $request->get('tag', '');

        if (empty($resourceId) || empty($tag)) {
            return new JsonResponse(
                array(
                    'error_text' => 'Not enough arguments - neither resource id, nor tag can be empty',
                    'content' => null,
                ),
                400
            );
        }

        $tags = $this->helper->addTag($contentId, $fieldId, $contentVersionId, $tag);

        return new JsonResponse($tags, 200);
    }

    /**
     * Removes the tag from remote resource and saves the updated value
     *
     * @param \Symfony\Component\HttpFoundation\Request $request
     * @param mixed $fieldId
     * @param mixed $contentVersionId
     *
     * @return \Symfony\Component\HttpFoundation\JsonResponse
     */
    public function removeTagsAction(Request $request, $contentId, $fieldId, $contentVersionId)
    {
        $this->checkContentPermissions($contentId);

        $resourceId = $request->get('id', '');
        $tag = $request->get('tag', '');

        if (empty($resourceId) || empty($tag)) {
            return new JsonResponse(
                array(
                    'error_text' => 'Not enough arguments - neither resource id, nor tag can be empty',
                    'content' => null,
                ),
                400
            );
        }

        $tags = $this->helper->removeTag($contentId, $fieldId, $contentVersionId, $tag);

        return new JsonResponse($tags, 200);
    }
}
