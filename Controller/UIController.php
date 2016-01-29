<?php

namespace Netgen\Bundle\RemoteMediaBundle\Controller;

use eZ\Bundle\EzPublishCoreBundle\Controller;
use ezote\operators\Json;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;

class UIController extends Controller
{
    public function uploadFileAction(Request $request)
    {
        $file = $request->files->get('file', '');
        $attributeId = $request->get('AttributeID', '');
        $contentVersion = $request->get('ContentObjectVersion', '');

        /*if (empty($file) || empty($attributeId) || empty($contentVersion)) {
            return new JsonResponse(
                array(
                    'ok' => false,
                    'error' => 'Not all arguments where set (file, attribute Id, content version)'
                ),
                400
            );
        }*/

        $attribute = $this->getLegacyKernel()->runCallback(
            function () use ($attributeId, $contentVersion)
            {
                return \eZContentObjectAttribute::fetch($attributeId, $contentVersion);
            }
        );

        $isRemoteMedia = $attribute->attribute('data_type_string') === 'ngremotemedia';

        if ($isRemoteMedia) {
            $provider = $this->container->get('netgen_remote_media.remote_media.provider');

            $result = $provider->upload($file->getRealPath());

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
}
