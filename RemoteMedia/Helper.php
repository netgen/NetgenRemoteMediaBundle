<?php

namespace Netgen\Bundle\RemoteMediaBundle\RemoteMedia;

use eZ\Publish\API\Repository\ContentService;
use eZ\Publish\API\Repository\ContentTypeService;
use eZ\Publish\SPI\Persistence\Content\Field;
use Netgen\Bundle\RemoteMediaBundle\Core\FieldType\RemoteMedia\Value;
use Netgen\Bundle\RemoteMediaBundle\RemoteMedia\RemoteMediaProviderInterface;

class Helper
{
    /**
     * @var \Netgen\Bundle\RemoteMediaBundle\RemoteMedia\RemoteMediaProviderInterface
     */
    protected $provider;

    /**
     * @var ContentService
     */
    protected $contentService;

    /**
     * @var ContentTypeService
     */
    protected $contentTypeService;

    /**
     * Helper constructor.
     * @param Netgen\Bundle\RemoteMediaBundle\Core\Persistence\Legacy\RemoteMedia\Handler $handler
     * @param \Netgen\Bundle\RemoteMediaBundle\RemoteMedia\RemoteMediaProviderInterface $provider
     */
    public function __construct(
        RemoteMediaProviderInterface $provider,
        ContentService $contentService,
        ContentTypeService $contentTypeService
    )
    {
        $this->provider = $provider;
        $this->contentService = $contentService;
        $this->contentTypeService = $contentTypeService;
    }

    protected function loadContent($contentId, $versionId, $languageCode = null)
    {
        $languageCode = empty($languageCode) ? $languageCode : array($languageCode);

        return $this->contentService->loadContent($contentId, $languageCode, $versionId);
    }

    public function loadField($contentId, $fieldId, $versionId, $languageCode = null)
    {
        $content = $this->loadContent($contentId, $versionId, $languageCode);
        $contentFields = $content->getFieldsByLanguage($languageCode);

        $currentField = null;
        foreach($contentFields as $field) {
            if ($field->id == $fieldId) {
                return $field;
            }
        }

        // @todo: fix exception
        throw new \InvalidArgumentException("Field not found");
    }

    /**
     * Loads the field value from the database
     *
     * @param mixed $fieldId
     * @param mixed $versionId
     *
     * @return \Netgen\Bundle\RemoteMediaBundle\Core\FieldType\RemoteMedia
     */
    public function loadValue($contentId, $fieldId, $versionId, $languageCode = null)
    {
        $field = $this->loadField($contentId, $fieldId, $versionId, $languageCode);

        return $field->value;
    }

    /**
     * Loads available formats for the field
     *
     * @param mixed $fieldId
     * @param mixed $versionId
     *
     * @return array
     */
    public function loadAvailableFormats($contentId, $fieldId, $versionId, $languageCode = null)
    {
        $field = $this->loadField($contentId, $fieldId, $versionId, $languageCode);

        $content = $this->loadContent($contentId, $versionId, $languageCode);
        $contentType = $this->contentTypeService->loadContentType($content->contentInfo->contentTypeId);
        $fieldDefinition = $contentType->getFieldDefinition($field->fieldDefIdentifier);
        $fieldSettings = $fieldDefinition->getFieldSettings();

        return !empty($fieldSettings['formats']) ? $fieldSettings['formats'] : array();
    }

    /**
     * Updates the field in the database with the provided value
     *
     * @param \Netgen\Bundle\RemoteMediaBundle\Core\FieldType\RemoteMedia\Value $value
     * @param mixed $fieldId
     * @param mixed $contentVersionId
     *
     * @return \Netgen\Bundle\RemoteMediaBundle\Core\FieldType\RemoteMedia\Value
     */
    public function updateValue($value, $contentId, $fieldId, $contentVersionId, $languageCode = null)
    {
        $field = $this->loadField($contentId, $fieldId, $contentVersionId, $languageCode);
        $versionInfo = $this->contentService->loadVersionInfoById($contentId, $contentVersionId);

        $contentUpdateStruct = $this->contentService->newContentUpdateStruct();
        $contentUpdateStruct->setField($field->fieldDefIdentifier, $value);
        $contentDraft = $this->contentService->updateContent($versionInfo, $contentUpdateStruct);

        return $value;
    }

    /**
     * Uploads the local file to the remote provider and returns new Value
     *
     * @param string $fileUri
     * @param string $fileName
     * @param mixed|null $fieldId
     * @param mixed|null $contentVersionId
     *
     * @return \Netgen\Bundle\RemoteMediaBundle\Core\FieldType\RemoteMedia\Value
     */
    public function upload($fileUri, $fileName, $fieldId = null, $contentVersionId = null)
    {
        if (!empty($fieldId) && !empty($contentVersionId)) {
            $folder = $fieldId . '/' . $contentVersionId;
        }

        $fileName = $this->filenameCleanUp($fileName);

        $options = array(
            'public_id' => $fileName.'/'.$folder,
            'overwrite' => true,
            'context' => array(
                'alt' => '',
                'caption' => '',
            ),
            'resource_type' => 'auto'
        );

        $response = $this->provider->upload($fileUri, $options);

        return $this->provider->getValueFromResponse($response);
    }

    public function getValueFromRemoteResource($resourceId, $resourceType)
    {
        $response = $this->provider->getRemoteResource($resourceId, $resourceType);

        return $this->provider->getValueFromResponse($response);
    }

    public function getVariationFromValue($value, $variantName, $availableFormats)
    {
        return $this->provider->getVariation($value, $availableFormats, $variantName);
    }

    /**
     * Adds the tag to the value
     *
     * @param $fieldId
     * @param $versionId
     * @param $tag
     *
     * @return array list of tags for the value
     */
    public function addTag($contentId, $fieldId, $versionId, $tag)
    {
        /** @var \Netgen\Bundle\RemoteMediaBundle\Core\FieldType\RemoteMedia\Value $value */
        $value = $this->loadValue($contentId, $fieldId, $versionId);
        $metaData = $value->metaData;
        $attributeTags = !empty($metaData['tags']) ? $metaData['tags'] : array();

        $result = $this->provider->addTagToResource($value->resourceId, $tag);
        $attributeTags[] = $tag;

        $metaData['tags'] = $attributeTags;
        $value->metaData = $metaData;

        $this->updateValue($value, $contentId, $fieldId, $versionId);

        return $attributeTags;
    }

    /**
     * Removes the tag from the value
     *
     * @param $fieldId
     * @param $versionId
     * @param $tag
     *
     * @return array list of tags for the value
     */
    public function removeTag($contentId, $fieldId, $versionId, $tag)
    {
        /** @var \Netgen\Bundle\RemoteMediaBundle\Core\FieldType\RemoteMedia\Value $value */
        $value = $this->loadValue($contentId, $fieldId, $versionId);
        $metaData = $value->metaData;
        $attributeTags = !empty($metaData['tags']) ? $metaData['tags'] : array();

        $result = $this->provider->removeTagFromResource($value->resourceId, $tag);
        $attributeTags = array_diff($attributeTags, array($tag));

        $metaData['tags'] = $attributeTags;
        $value->metaData = $metaData;

        $this->updateValue($value, $contentId, $fieldId, $versionId);

        return $attributeTags;

    }

    /**
     * Cleans up the file name for uploading
     *
     * @param string $fileName
     *
     * @return string
     */
    protected function filenameCleanUp($fileName)
    {
        $clean = preg_replace("/[^\p{L}|\p{N}]+/u", '_', $fileName);
        $cleanFileName = preg_replace("/[\p{Z}]{2,}/u", '_', $clean);

        return rtrim($cleanFileName, '_');
    }
}
