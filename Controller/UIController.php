<?php

namespace Netgen\Bundle\RemoteMediaBundle\Controller;

use eZ\Bundle\EzPublishCoreBundle\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\JsonResponse;
use Cloudinary\Api\NotFound;


/**
 * @todo: replace all legacy calls with new db gateway which will fetch the data from the db rows
 *          and then use converter and field type to get the field value
 *
 *          explore using DoctrineDatabase gateway
 *          vendor/ezsystems/ezpublish-kernel/eZ/Publish/Core/Persistence/Legacy/Content/Gateway/DoctrineDatabase.php
 */
class UIController extends Controller
{
    public function uploadFileAction(Request $request)
    {
        $file = $request->files->get('file', '');
        $attributeId = $request->get('AttributeID', '');
        $contentVersionId = $request->get('ContentObjectVersion', '');

        if (empty($file) || empty($attributeId) || empty($contentVersionId)) {
            return new JsonResponse(
                array(
                    'ok' => false,
                    'error' => 'Not all arguments where set (file, attribute Id, content version)',
                ),
                400
            );
        }

        $dbHandler = $this->get('ezpublish.api.storage_engine.legacy.handler.ngremotemedia');

        /** @var \eZ\Publish\SPI\Persistence\Content\Field $field */
        $field = $dbHandler->load($attributeId, $contentVersionId);

        if ($field->type !== 'ngremotemedia') {
            return new JsonResponse(
                array(
                    'error_text' => 'Attribute is of the wrong field type',
                )
            );
        }

        $provider = $this->container->get('netgen_remote_media.remote_media.provider');

        $fileUri = $file->getRealPath();
        $folder = $attributeId.'/'.$contentVersionId;

        // clean up file name
        $fileName = pathinfo($file->getClientOriginalName(), PATHINFO_FILENAME);
        $clean = preg_replace("/[^\p{L}|\p{N}]+/u", '_', $fileName);
        $cleanFileName = preg_replace("/[\p{Z}]{2,}/u", '_', $clean);
        $cleanFileName = rtrim($cleanFileName, '_');

        $options = array(
            'public_id' => $cleanFileName.'/'.$folder,
            'overwrite' => true,
            'context' => array(
                'alt' => '',
                'caption' => '',
            ),
        );

        $result = $provider->upload($fileUri, $options);

        $value = $provider->getValueFromResponse($result);
        $dbHandler->update($value, $attributeId, $contentVersionId);

        $content = $this->renderView(
            'NetgenRemoteMediaBundle:ezexceed/test:ngremotemedia.html.twig',
            array(
                'value' => $value,
                'fieldId' => $attributeId,
                'availableFormats' => $availableFormats
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

//    public function fetchAction(Request $request, $attributeId, $contentVersionId)
//    {
//        $attribute = $this->legacyGetAttribute($attributeId, $contentVersionId);
//
//        /** @var \Netgen\Bundle\RemoteMediaBundle\Core\FieldType\RemoteMedia\Value $data */
//        $data = $attribute->Content();
//        $variations = $data->variations;
//
//        $content = $this->renderView(
//            'NetgenRemoteMediaBundle:ezexceed/edit:ngremotemedia.html.twig',
//            array(
//                'attribute' => $attribute,
//            )
//        );
//
//        $scaling = array();
//        foreach ($variations as $name => $coords) {
//            $scaling[] = array(
//                'name' => $name,
//                'coords' => array(
//                    (int) $coords['x'],
//                    (int) $coords['y'],
//                    (int) $coords['x'] + (int) $coords['w'],
//                    (int) $coords['y'] + (int) $coords['h'],
//                ),
//            );
//        }
//
//        $responseData = array(
//            'media' => !empty($data->resourceId) ? $data : false,
//            'content' => $content,
//            'toScale' => $scaling,
//        );
//
//        return new JsonResponse($responseData, 200);
//    }

    public function fetchTestAction(Request $request, $attributeId, $contentVersionId)
    {
        $dbHandler = $this->get('ezpublish.api.storage_engine.legacy.handler.ngremotemedia');

        /** @var \eZ\Publish\SPI\Persistence\Content\Field $field */
        $field = $dbHandler->load($attributeId, $contentVersionId);

        $fieldSettings = $dbHandler->loadFieldSettings($field->fieldDefinitionId);
        $availableFormats = !empty($fieldSettings['formats']) ? $fieldSettings['formats'] : array();

        /** @var \Netgen\Bundle\RemoteMediaBundle\Core\FieldType\RemoteMedia\Value $value */
        $value = $field->value;
        $variations = $value->variations;

        $content = $this->renderView(
            'NetgenRemoteMediaBundle:ezexceed/test:ngremotemedia.html.twig',
            array(
                'value' => $value,
                'fieldId' => $attributeId,
                'availableFormats' => $availableFormats
            )
        );

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

        $responseData = array(
            'media' => !empty($value->resourceId) ? $value : false,
            'content' => $content,
            'toScale' => $scaling,
        );

        return new JsonResponse($responseData, 200);
    }

    // used for ezoe
//    public function fetchRemoteAction(Request $request, $id)
//    {
//        $provider = $this->get('netgen_remote_media.remote_media.provider');
//
//        try {
//            $resource = $provider->getRemoteResource($id);
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

//    public function saveAttributeAction(Request $request, $objectId, $attributeId, $contentVersionId)
//    {
//        // make all coords int
//        $variantName = $request->get('name', '');
//        $crop_x = $request->get('crop_x', 0);
//        $crop_y = $request->get('crop_y', 0);
//        $crop_w = $request->get('crop_w', 0);
//        $crop_h = $request->get('crop_h', 0);
//
//        if (empty($variantName) || empty($crop_w) || empty($crop_h)) {
//            throw new \InvalidArgumentException('Missing one of the arguments: variant name, crop width, crop height');
//        }
//
//        $contentService = $this->getRepository()->getContentService();
//
//        $attribute = $this->legacyGetAttribute($attributeId, $contentVersionId);
//
//        $isKeymediaAttribute = ($attribute->attribute('data_type_string') == 'ngremotemedia' ? true : false);
//
//        if (!$isKeymediaAttribute) {
//            return new JsonResponse('Error', 500);
//        }
//
//        $versionObject = $this->getLegacyKernel()->runCallback(
//            function () use ($attribute) {
//                return \eZContentObjectVersion::fetchVersion(
//                    $attribute->attribute('version'), $attribute->attribute('contentobject_id')
//                );
//            }
//        );
//
//        if (!$versionObject instanceof \eZContentObjectVersion) {
//            return new JsonResponse('Error', 500);
//        }
//
//        $versionObject->setAttribute('modified', time());
//        if ($versionObject->attribute('status') == \eZContentObjectVersion::STATUS_INTERNAL_DRAFT) {
//            $versionObject->setAttribute('status', \eZContentObjectVersion::STATUS_DRAFT);
//        }
//
//        /** @var \Netgen\Bundle\RemoteMediaBundle\Core\FieldType\RemoteMedia\Value $value */
//        $value = $attribute->Content();
//
//        $variations = $value->variations;
//        $variationCoords = array(
//            $variantName => array(
//                'x' => $crop_x,
//                'y' => $crop_y,
//                'w' => $crop_w,
//                'h' => $crop_h,
//            ),
//        );
//
//        $emptyCoords = array(
//            'x' => 0,
//            'y' => 0,
//            'w' => 0,
//            'h' => 0,
//        );
//
//        $contentClassAttribute = $attribute->contentClassAttribute();
//        $attributeVariations = json_decode($contentClassAttribute->attribute('data_text4'), true);
//        $initalVariations = array();
//        foreach ($attributeVariations as $name => $key) {
//            $initalVariations[$name] = $emptyCoords;
//        }
//
//        $variations = $variationCoords + $variations + $initalVariations;
//
//        $value->variations = $variations;
//        $attribute->setAttribute('data_text', json_encode($value));
//        $versionObject->setAttribute('remote_image', $attribute);
//
//        $attribute = $this->legacySaveAttribute($attribute, $versionObject);
//
//        $provider = $this->container->get('netgen_remote_media.remote_media.provider');
//        $variation = $provider->getVariation(
//            $value,
//            $attributeVariations,
//            $variantName
//        );
//
//        $responseData = array(
//            'error_text' => '',
//            'content' => array(
//                'name' => $variantName,
//                'url' => $variation->url,
//                'coords' => array(
//                    (int) $crop_x,
//                    (int) $crop_y,
//                    (int) $crop_x + (int) $crop_w,
//                    (int) $crop_y + (int) $crop_h,
//                ),
//            ),
//        );
//
//        return new JsonResponse($responseData, 200);
//    }

    public function saveAttributeTestAction(Request $request, $objectId, $attributeId, $contentVersionId)
    {
        // make all coords int
        $variantName = $request->get('name', '');
        $crop_x = $request->get('crop_x', 0);
        $crop_y = $request->get('crop_y', 0);
        $crop_w = $request->get('crop_w', 0);
        $crop_h = $request->get('crop_h', 0);

        if (empty($variantName) || empty($crop_w) || empty($crop_h)) {
            throw new \InvalidArgumentException('Missing one of the arguments: variant name, crop width, crop height');
        }

        $contentService = $this->getRepository()->getContentService();

        $dbHandler = $this->get('ezpublish.api.storage_engine.legacy.handler.ngremotemedia');

        /** @var \eZ\Publish\SPI\Persistence\Content\Field $field */
        $field = $dbHandler->load($attributeId, $contentVersionId);

        $fieldSettings = $dbHandler->loadFieldSettings($field->fieldDefinitionId);
        $availableFormats = !empty($fieldSettings['formats']) ? $fieldSettings['formats'] : array();

        /** @var \Netgen\Bundle\RemoteMediaBundle\Core\FieldType\RemoteMedia\Value $value */
        $value = $field->value;
        $variations = $value->variations;


        if ($field->type !== 'ngremotemedia') {
            return new JsonResponse('Error', 500);
        }

        $variationCoords = array(
            $variantName => array(
                'x' => $crop_x,
                'y' => $crop_y,
                'w' => $crop_w,
                'h' => $crop_h,
            ),
        );

        $emptyCoords = array(
            'x' => 0,
            'y' => 0,
            'w' => 0,
            'h' => 0,
        );

        $initalVariations = array();
        foreach ($availableFormats as $name => $key) {
            $initalVariations[$name] = $emptyCoords;
        }

        $variations = $variationCoords + $variations + $initalVariations;
        $value->variations = $variations;

        $dbHandler->update($value, $attributeId, $contentVersionId);

        $provider = $this->container->get('netgen_remote_media.remote_media.provider');
        $variation = $provider->getVariation(
            $value,
            $availableFormats,
            $variantName
        );

        $responseData = array(
            'error_text' => '',
            'content' => array(
                'name' => $variantName,
                'url' => $variation->url,
                'coords' => array(
                    (int) $crop_x,
                    (int) $crop_y,
                    (int) $crop_x + (int) $crop_w,
                    (int) $crop_y + (int) $crop_h,
                ),
            ),
        );

        return new JsonResponse($responseData, 200);
    }

    public function browseRemoteMediaAction(Request $request, $attributeId, $contentVersionId)
    {
        $offset = $request->get('offset', 0);

        $hardLimit = 500;
        $limit = 25;
        $query = $request->get('q', '');

        $provider = $this->get('netgen_remote_media.remote_media.provider');

        if (empty($query)) {
            $list = $provider->listResources($hardLimit);
        } else {
            $list = $provider->searchResources($query, 'image', $hardLimit);
        }

        $count = count($list);

        $list = array_slice($list, $offset, $limit);

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
                    'url' => $provider->getFormattedUrl($hit['public_id'], $options),
                ),
            );
        }

        $results = array(
            'total' => $count,
            'hits' => $listFormatted,
        );

        $responseData = array(
            'keymediaId' => 0,
            'results' => $results,
        );

        return new JsonResponse($responseData, 200);
    }

    public function addTagsAction(Request $request, $attributeId, $contentVersionId)
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

        $provider = $this->get('netgen_remote_media.remote_media.provider');

        $dbHandler = $this->get('ezpublish.api.storage_engine.legacy.handler.ngremotemedia');

        /** @var \eZ\Publish\SPI\Persistence\Content\Field $field */
        $field = $dbHandler->load($attributeId, $contentVersionId);

        /** @var \Netgen\Bundle\RemoteMediaBundle\Core\FieldType\RemoteMedia\Value $value */
        $value = $field->value;
        $metaData = $value->metaData;
        $attributeTags = !empty($metaData['tags']) ? $metaData['tags'] : array();

        $result = $provider->addTagToResource($resourceId, $tag);
        $attributeTags[] = $tag;

        $metaData['tags'] = $attributeTags;
        $value->metaData = $metaData;

        $dbHandler->update($value, $attributeId, $contentVersionId);

        return new JsonResponse($attributeTags, 200);
    }

    public function removeTagsAction(Request $request, $attributeId, $contentVersionId)
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

        $provider = $this->get('netgen_remote_media.remote_media.provider');

        $dbHandler = $this->get('ezpublish.api.storage_engine.legacy.handler.ngremotemedia');

        /** @var \eZ\Publish\SPI\Persistence\Content\Field $field */
        $field = $dbHandler->load($attributeId, $contentVersionId);

        /** @var \Netgen\Bundle\RemoteMediaBundle\Core\FieldType\RemoteMedia\Value $value */
        $value = $field->value;
        $metaData = $value->metaData;
        $attributeTags = !empty($metaData['tags']) ? $metaData['tags'] : array();

        $result = $provider->removeTagFromResource($resourceId, $tag);
        $attributeTags = array_diff($attributeTags, array($tag));

        $metaData['tags'] = $attributeTags;
        $value->metaData = $metaData;
        $dbHandler->update($value, $attributeId, $contentVersionId);

        return new JsonResponse($attributeTags, 200);

    }

//    protected function legacyGetAttribute($attributeId, $contentVersionId)
//    {
//        return $this->getLegacyKernel()->runCallback(
//            function () use ($attributeId, $contentVersionId) {
//                return \eZContentObjectAttribute::fetch($attributeId, $contentVersionId);
//            }
//        );
//    }
//
//    protected function legacySaveAttribute($attribute, $versionObject = false)
//    {
//        return $this->getLegacyKernel()->runCallback(
//            function () use ($attribute, $versionObject) {
//                $attribute->store();
//                if ($versionObject) {
//                    $versionObject->store();
//                }
//
//                return $attribute;
//            }
//        );
//    }
}
