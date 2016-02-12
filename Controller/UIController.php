<?php

namespace Netgen\Bundle\RemoteMediaBundle\Controller;

use eZ\Bundle\EzPublishCoreBundle\Controller;
use eZ\Publish\API\Repository\Repository;
use Netgen\Bundle\RemoteMediaBundle\Core\FieldType\RemoteMedia\Value;
use Netgen\Bundle\RemoteMediaBundle\RemoteMedia\Helper;
use Netgen\Bundle\RemoteMediaBundle\RemoteMedia\RemoteMediaProviderInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\JsonResponse;
use Cloudinary\Api\NotFound;
use Symfony\Component\Templating\EngineInterface;

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

    public function __construct(RemoteMediaProviderInterface $provider, Helper $helper, EngineInterface $templating)
    {
        $this->provider = $provider;
        $this->helper = $helper;
        $this->templating = $templating;
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
        $file = $request->files->get('file', '');
        $fieldId = $request->get('AttributeID', '');
        $contentVersionId = $request->get('ContentObjectVersion', '');
        $legacy = (bool) $request->get('legacy', false);

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
                'contentObjectId' => $contentId
            )
        );

        $result = json_decode($value->__toString(), true);

        $result['id'] = $value->resourceId;
        $result['scalesTo'] = array(
            'quality' => 100,
            'ending' => $value->metaData['format'],
        );

        return new JsonResponse(
            array(
                'error_text' => '',
                'content' => array(
                    'media' => $result,
                    'toScale' == ''/*$handler->attribute('toscale')*/,
                    'content' => $content,
                    'ok' => true,
                ),
            ),
            200
        );
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
        /** @var \Netgen\Bundle\RemoteMediaBundle\Core\FieldType\RemoteMedia\Value $value */
        $value = $this->helper->loadValue($contentId, $fieldId, $contentVersionId);

        $content = $this->templating->render(
            'NetgenRemoteMediaBundle:ezexceed/edit:ngremotemedia.html.twig',
            array(
                'value' => $value,
                'fieldId' => $fieldId,
                'availableFormats' => $this->helper->loadAvailableFormats($contentId, $fieldId, $contentVersionId)
            )
        );

        $responseData = array(
            'media' => !empty($value->resourceId) ? $value : false,
            'content' => $content,
            'toScale' => $this->getScaling($value),
        );

        return new JsonResponse($responseData, 200);
    }

    // used for ezoe
//    public function fetchRemoteAction(Request $request, $id)
//    {
//
//        try {
//            $resource = $this->provider->getRemoteResource($id);
//        } catch (NotFound $e) {
//            return new JsonResponse(
//                array(
//                    'error_text' => $e->getMessage(),
//                )
//            );
//        }
//
//        $versions = $this->getConfigResolver()->getParameter('ezoe.variation_list', 'netgen_remote_media');
//        $toScale = array();
//        if (!empty($versions) && is_array($versions)) {
//            foreach ($versions as $name => $size) {
//                $size = array_map(function ($value) { return (int) $value;}, explode('x', $size));
//
//                if (count($size) != 2 || !is_integer($size[0]) && !is_integer($size[1])) {
//                    continue;
//                }
//                /*
//                 * Both dimensions can't be unbound
//                 */
//                if ($size[0] == 0 || $size[1] == 0) {
//                    continue;
//                }
//
//                $toScale[] = array(
//                    'name' => $name,
//                    'size' => $size,
//                );
//            }
//        }
//
//        $classList = $this->getConfigResolver()->getParameter('ezoe.class_list', 'netgen_remote_media');
//        $viewModes = $this->getConfigResolver()->getParameter('ezoe.view_modes', 'netgen_remote_media');
//
//        return new JsonResponse(
//            compact('resource', 'toScale', 'classList', 'viewModes'),
//            200
//        );
//    }

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
        // @todo: make all coords int
        $variantName = $request->request->getAlnum('name', '');
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

        /** @var \Netgen\Bundle\RemoteMediaBundle\Core\FieldType\RemoteMedia\Value $value */
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
            'error_text' => '',
            'content' => array(
                'name' => $variantName,
                'url' => $variation->url,
                'coords' => array(
                    $crop_x,
                    $crop_y,
                    $crop_x + $crop_w,
                    $crop_y + $crop_h,
                ),
            ),
        );

        return new JsonResponse($responseData, 200);
    }

    /**
     * Legacy admin:
     * Called when media is selected from the list of uploaded resources
     *
     * @param Request $request
     * @param $contentId
     * @param $fieldId
     * @param $contentVersionId
     *
     * @return JsonResponse
     */
    public function saveAttributeLegacyAction(Request $request, $contentId, $fieldId, $contentVersionId)
    {
        $userId = $request->get('user_id', null);
        $this->checkPermissions($contentId, $userId);

        $resourceId = $request->get('resource_id', '');
        $languageCode = $request->get('language_code', null);

        if (empty($resourceId)) {
            return new JsonResponse(
                array(
                    'error_text' => 'Resource id must not be empty',
                    'content' => null,
                ),
                400
            );
        }

        $updatedValue = $this->helper->getValueFromRemoteResource($resourceId, 'image');
        $value = $this->helper->updateValue($updatedValue, $contentId, $fieldId, $contentVersionId, $languageCode);

        $content = $this->templating->render(
            'file:extension/ngremotemedia/design/standard/templates/content/datatype/edit/ngremotemedia.tpl',
            array(
                'value' => $value,
                'attributeId' => $fieldId,
                'variations' => $this->helper->loadAvailableFormats($contentId, $fieldId, $contentVersionId),
                'version' => $contentVersionId,
                'contentObjectId' => $contentId,
                'ajax' => true
            )
        );

        $responseData = array(
            'media' => !empty($value->resourceId) ? $value : false,
            'content' => $content,
            'toScale' => $this->getScaling($value),
        );

        if (!empty($userId)) {
            $anonymousUser = $this->repository->getUserService()->loadUser($this->anonymousUserId);
            $this->repository->setCurrentUser($anonymousUser);
        }

        return new JsonResponse($responseData, 200);
    }

    /**
     * eZExceed:
     * Fetches the list of available images from remote provider
     *
     * @param \Symfony\Component\HttpFoundation\Request $request
     * @param mixed $fieldId
     * @param mixed $contentVersionId
     *
     * @return \Symfony\Component\HttpFoundation\JsonResponse
     */
    public function browseRemoteMediaAction(Request $request, $contentId, $fieldId, $contentVersionId)
    {
        $hardLimit = 500;
        $limit = 25;
        $query = $request->get('q', '');
        $offset = $request->get('offset', 0);

        $list = $this->helper->searchResources($query, $offset, $limit, $this->browseLimit);

        $responseData = array(
            'keymediaId' => 0,
            'results' => array(
                'total' => $list['count'],
                'hits' => $list['hits'],
            ),
        );

        return new JsonResponse($responseData, 200);
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
