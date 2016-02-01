<?php

namespace Netgen\Bundle\RemoteMediaBundle\Controller;

use eZ\Bundle\EzPublishCoreBundle\Controller;
use Netgen\Bundle\RemoteMediaBundle\Core\FieldType\RemoteMedia\Value;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;

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
                    'error' => 'Not all arguments where set (file, attribute Id, content version)'
                ),
                400
            );
        }

        $attribute = $this->getLegacyKernel()->runCallback(
            function () use ($attributeId, $contentVersionId)
            {
                return \eZContentObjectAttribute::fetch($attributeId, $contentVersionId);
            }
        );

        $isRemoteMedia = $attribute->attribute('data_type_string') === 'ngremotemedia';

        if ($isRemoteMedia) {
            $provider = $this->container->get('netgen_remote_media.remote_media.provider');

            $fileUri = $file->getRealPath();
            $folder = $attributeId . '/' . $contentVersion;
            $options = array(
                'public_id' => pathinfo($fileUri, PATHINFO_FILENAME) . '/' . $folder,
                'overwrite' => true,
                'context' => array(
                    'alt' => '',
                    'caption' => '',
                ),
            );

            $result = $provider->upload(
                $fileUri,
                $options
            );

            $content = $this->renderView('design:content/datatype/edit/ngremotemedia.tpl', array(
                'attribute' => $attribute,
                'uploadedFile' => $result
            ));

            $result['id'] = $result['public_id'];
            $result['scalesTo'] = array(
                'quality' => 100,
                'ending' => $result['format']
            );


            return new JsonResponse(
                array(
                    'error_text' => '',
                    'content' => array(
                        'media' => $result,
                        'toScale' == ''/*$handler->attribute('toscale')*/,
                        'content' => $content,
                        'ok' => true
                    )
                ),
                200
            );
        }
    }

    public function fetchAction(Request $request, $attributeId, $contentVersionId)
    {
        $attribute = $this->getLegacyKernel()->runCallback(
            function () use ($attributeId, $contentVersionId)
            {
                return \eZContentObjectAttribute::fetch($attributeId, $contentVersionId);
            }
        );

        $data = json_decode($attribute->attribute('data_text'), true);
        $variations = $data['variations'];

        $content = $this->renderView('design:content/datatype/edit/ngremotemedia.tpl', array(
            'attribute' => $attribute
        ));

        $scaling = array();
        foreach($variations as $name => $coords) {
            $scaling[] = array(
                'name' => $name,
                'coords' => $coords
            );
        }

        $responseData = array(
            'media' => $data,
            'content' => $content,
            'toScale' => $scaling
        );

        return new JsonResponse($responseData, 200);
    }

    public function saveAttributeAction(Request $request, $objectId, $attributeId, $contentVersionId)
    {
        $variantName = $request->get('name', '');
        $variantSize = $request->get('size', array());
        $crop_x = $request->get('crop_x', 0);
        $crop_y = $request->get('crop_y', 0);
        $crop_w = $request->get('crop_w', 0);
        $crop_h = $request->get('crop_h', 0);

        if (empty($variantName) || empty($crop_w) || empty($crop_h)) {
            throw new \InvalidArgumentException('Missing one of the arguments: variant name, crop width, crop height');
        }

        $contentService = $this->getRepository()->getContentService();

        $attribute = $this->getLegacyKernel()->runCallback(
            function () use ($attributeId, $contentVersionId)
            {
                return \eZContentObjectAttribute::fetch($attributeId, $contentVersionId);
            }
        );

        $isKeymediaAttribute = ($attribute->attribute('data_type_string') == 'ngremotemedia' ? true : false);

        if (!$isKeymediaAttribute) {
            return new JsonResponse('Error', 500);
        }

        $versionObject = $this->getLegacyKernel()->runCallback(
            function () use ($attribute)
            {
                return \eZContentObjectVersion::fetchVersion(
                    $attribute->attribute('version'), $attribute->attribute('contentobject_id')
                );
            }
        );

        if (!$versionObject instanceof \eZContentObjectVersion) {
            return new JsonResponse('Error', 500);
        }

        $versionObject->setAttribute('modified', time());
        if ($versionObject->attribute('status') == \eZContentObjectVersion::STATUS_INTERNAL_DRAFT) {
            $versionObject->setAttribute('status', \eZContentObjectVersion::STATUS_DRAFT);
        }

        $value = json_decode($attribute->attribute('data_text'), true);

        $variations = $value['variations'];

        $variationCoords = array(
            $variantName => array(
                'x' => $crop_x,
                'y' => $crop_y,
                'w' => $crop_w,
                'h' => $crop_h
            )
        );

        $variations = array(
            'Small' => array(
                'x' => 0,
                'y' => 0,
                'w' => 0,
                'h' => 0
            ),
            'Medium' => array(
                'x' => 0,
                'y' => 0,
                'w' => 0,
                'h' => 0
            ),
            'Big' => array(
                'x' => 0,
                'y' => 0,
                'w' => 0,
                'h' => 0
            )
        );

        $variations = $variationCoords + $variations;

        $value['variations'] = $variations;
        $attribute->setAttribute('data_text', json_encode($value));
        $versionObject->setAttribute('remote_image', $attribute);

        $this->getLegacyKernel()->runCallback(
            function () use ($attribute, $versionObject)
            {
                $attribute->store();
                $versionObject->store();
                return;
            }
        );

        $provider = $this->container->get('netgen_remote_media.remote_media.provider');

        $variation = $provider->getVariation(
            new Value($value),
            array(
                'Small' => '200x200',
                'Medium' => '400x400',
                'Big' => '800x600'
            ),
            $variantName
        );

        $responseData = array(
            'error_text' => '',
            'content' => array(
                'name' => $variantName,
                'url' => $variation->url,
                'coords' => array(
                    $crop_x,
                    $crop_y,
                    $crop_w,
                    $crop_h,
                ),
                'size' => array(
                    $variantSize[0],
                    $variantSize[1]
                )
            )
        );

        return new JsonResponse($responseData, 200);
    }
}
