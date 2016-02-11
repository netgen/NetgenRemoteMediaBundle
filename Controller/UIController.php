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
                    'error' => 'Not all arguments where set (file, attribute Id, content version)',
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

        $this->helper->updateValue($value, $fieldId, $contentVersionId);

        $content = $this->templating->render(
            $template,
            array(
                'value' => $value,
                'fieldId' => $fieldId,
                'availableFormats' => $this->helper->loadAvailableFormats($fieldId, $contentVersionId),
                'version' => $contentVersionId,
                'contentObjectId' => $contentId
            )
        );

        $result['id'] = $result['public_id'];
        $result['scalesTo'] = array(
            'quality' => 100,
            'ending' => $result['format'],
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
        $value = $this->helper->loadValue($fieldId, $contentVersionId);
        $availableFormats = $this->helper->loadAvailableFormats($fieldId, $contentVersionId);

        $content = $this->templating->render(
            'NetgenRemoteMediaBundle:ezexceed/edit:ngremotemedia.html.twig',
            array(
                'value' => $value,
                'fieldId' => $fieldId,
                'availableFormats' => $availableFormats
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
//                    'error' => $e->getMessage(),
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
            throw new \InvalidArgumentException('Missing one of the arguments: variant name, crop width, crop height');
        }

        /** @var \Netgen\Bundle\RemoteMediaBundle\Core\FieldType\RemoteMedia\Value $value */
        $value = $this->helper->loadValue($fieldId, $contentVersionId);
        $availableFormats = $this->helper->loadAvailableFormats($fieldId, $contentVersionId);

        $variationCoords = array(
            $variantName => array(
                'x' => $crop_x,
                'y' => $crop_y,
                'w' => $crop_w,
                'h' => $crop_h,
            ),
        );

        // @todo: do we need this here?
        $initalVariations = array();
        foreach ($availableFormats as $name => $key) {
            $initalVariations[$name] = array(
                'x' => 0,
                'y' => 0,
                'w' => 0,
                'h' => 0,
            );
        }

        $variations = $variationCoords + $value->variations + $initalVariations;
        $value->variations = $variations;

        $this->helper->updateValue($value, $fieldId, $contentVersionId);

        $variation = $this->helper->getVariationFromValue($value, $variantName, $availableFormats);

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
    public function saveAttributeLegacy(Request $request, $contentId, $fieldId, $contentVersionId)
    {
        $resourceId = $this->get('resourceId', '');

        if (empty($resourceId)) {
            throw new \InvalidArgumentException('Resource id must not be empty');
        }

        $updatedValue = $this->helper->getValueFromRemoteResource($data['public_id'], 'image');
        $value = $this->helper->updateValue($updatedValue, $fieldId, $contentVersionId);

        $content = $this->templating->render(
            'file:extension/ngremotemedia/design/standard/templates/content/datatype/edit/ngremotemedia.tpl',
            array(
                'value' => $value,
                'attributeId' => $fieldId,
                'variations' => $this->helper->loadAvailableFormats($fieldId, $contentVersionId),
                'version' => $contentVersionId,
                'contentObjectId' => $contentId
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
     * Formats the list to comply with the ezexceed
     *
     * @todo: maybe this should be part of the provider implementation as it is provider specific?
     *
     * @param array $list
     *
     * @return array
     */
    protected function formatBrowseList(array $list)
    {
        $listFormatted = array();
        foreach ($list as $hit) {
            $fileName = explode('/', $hit['public_id']);
            $fileName = $fileName[0];

            $options = array();
            $options['crop'] = 'fit';
            $options['width'] = 160;
            $options['height'] = 120;

            $listFormatted[] = array(
                'id' => $hit['public_id'],
                'tags' => $hit['tags'],
                'type' => $hit['resource_type'],
                'filesize' => $hit['bytes'],
                'width' => $hit['width'],
                'height' => $hit['height'],
                'filename' => $fileName,
                'shared' => array(),
                'scalesTo' => array('quality' => 100, 'ending' => $hit['format']),
                'host' => 'cloudinary',
                'thumb' => array(
                    'url' => $this->provider->getFormattedUrl($hit['public_id'], $options),
                ),
            );
        }

        return $listFormatted;
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

        if (empty($query)) {
            $list = $this->provider->listResources($hardLimit);
        } else {
            $list = $this->provider->searchResources($query, 'image', $hardLimit);
        }

        $count = count($list);
        $listFormatted = $this->formatBrowseList(array_slice($list, $offset, $limit));

        $responseData = array(
            'keymediaId' => 0,
            'results' => array(
                'total' => $count,
                'hits' => $listFormatted,
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

        $tags = $this->helper->addTag($fieldId, $contentVersionId, $tag);

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

        $tags = $this->helper->removeTag($fieldId, $contentVersionId, $tag);
        return new JsonResponse($tags, 200);

    }
}
