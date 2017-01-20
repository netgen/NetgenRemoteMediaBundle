<?php

namespace Netgen\Bundle\RemoteMediaBundle\RemoteMedia;

use eZ\Publish\API\Repository\ContentService;
use eZ\Publish\API\Repository\ContentTypeService;
use eZ\Publish\Core\Base\Exceptions\NotFoundException;

class Helper
{
    /**
     * @var \Netgen\Bundle\RemoteMediaBundle\RemoteMedia\RemoteMediaProvider
     */
    protected $provider;

    /**
     * @var \eZ\Publish\API\Repository\ContentService
     */
    protected $contentService;

    /**
     * @var \eZ\Publish\API\Repository\ContentTypeService
     */
    protected $contentTypeService;

    /**
     * Should content-version and field-id be a part of the filename sent to the remote provider?
     * @var bool
     */
    protected $contentVersionInFileName = true;

    /**
     * Helper constructor.
     *
     * @param \Netgen\Bundle\RemoteMediaBundle\RemoteMedia\RemoteMediaProvider $provider
     * @param \eZ\Publish\API\Repository\ContentService $contentService
     * @param \eZ\Publish\API\Repository\ContentTypeService $contentTypeService
     */
    public function __construct(
        RemoteMediaProvider $provider,
        ContentService $contentService,
        ContentTypeService $contentTypeService
    )
    {
        $this->provider = $provider;
        $this->contentService = $contentService;
        $this->contentTypeService = $contentTypeService;
    }

    public function setContentVersionInFileName($contentVersionInFileName = null)
    {
        $this->contentVersionInFileName = $contentVersionInFileName;
    }

    /**
     * Loads the content.
     *
     * @param mixed $contentId
     * @param mixed|null $versionId
     * @param string|null $languageCode
     *
     * @return \eZ\Publish\API\Repository\Values\Content\Content
     */
    protected function loadContent($contentId, $versionId = null, $languageCode = null)
    {
        $languageCode = empty($languageCode) ? $languageCode : array($languageCode);

        return $this->contentService->loadContent($contentId, $languageCode, $versionId);
    }

    /**
     * Loads the field.
     *
     * @param mixed $contentId
     * @param mixed $fieldId
     * @param mixed|null $versionId
     * @param string|null $languageCode
     *
     * @throws NotFoundException if field is not found
     *
     * @return \eZ\Publish\API\Repository\Values\Content\Field
     */
    protected function loadField($contentId, $fieldId, $versionId = null, $languageCode = null)
    {
        $content = $this->loadContent($contentId, $versionId, $languageCode);
        $languageCode = $languageCode ?: $content->getVersionInfo()->initialLanguageCode;
        $contentFields = $content->getFieldsByLanguage($languageCode);

        foreach($contentFields as $field) {
            if ($field->id == $fieldId) {
                return $field;
            }
        }

        throw new NotFoundException('field', $fieldId);
    }

    /**
     * Loads the field value.
     *
     * @param mixed $contentId
     * @param mixed $fieldId
     * @param mixed|null $versionId
     * @param string|null $languageCode
     *
     * @return \Netgen\Bundle\RemoteMediaBundle\Core\FieldType\RemoteMedia\Value
     */
    public function loadValue($contentId, $fieldId, $versionId = null, $languageCode = null)
    {
        $field = $this->loadField($contentId, $fieldId, $versionId, $languageCode);

        return $field->value;
    }

    /**
     * Updates the field value.
     *
     * @param \Netgen\Bundle\RemoteMediaBundle\Core\FieldType\RemoteMedia\Value $value
     * @param mixed $contentId
     * @param mixed $fieldId
     * @param mixed $contentVersionId
     * @param string|null $languageCode
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
     * Uploads the local file to the remote provider and returns new Value.
     * This method DOES NOT save the new Value!
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
        if ($this->contentVersionInFileName) {
            if (!empty($fieldId) && !empty($contentVersionId)) {
                $folder = $fieldId.'/'.$contentVersionId;
            } else {
                $folder = '';
            }
        }

        $fileName = $this->filenameCleanUp($fileName);
        $id = empty($folder) ? $fileName : $fileName.'/'.$folder;

        $options = $this->provider->prepareUploadOptions($id);
        $response = $this->provider->upload($fileUri, $options);

        return $this->provider->getValueFromResponse($response);
    }

    /**
     * Fetches remote resource and creates new Value with it.
     *
     * @param string $resourceId
     * @param string $resourceType
     *
     * @return \Netgen\Bundle\RemoteMediaBundle\Core\FieldType\RemoteMedia\Value
     */
    public function getValueFromRemoteResource($resourceId, $resourceType)
    {
        return $this->provider->getRemoteResource($resourceId, $resourceType);
    }

    /**
     * Returns the Variation for the value.
     *
     * @param \Netgen\Bundle\RemoteMediaBundle\Core\FieldType\RemoteMedia\Value $value
     * @param string $variantName
     * @param array $availableFormats
     *
     * @return \Netgen\Bundle\RemoteMediaBundle\Core\FieldType\RemoteMedia\Variation
     */
    public function getVariationFromValue($value, $variantName, $availableFormats, $secure = true)
    {
        return $this->provider->getVariation($value, $variantName, $availableFormats, $secure);
    }

    /**
     * Adds the tag to the field value.
     *
     * @param mixed $contentId
     * @param mixed $fieldId
     * @param mixed $versionId
     * @param string $tag
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
     * Removes the tag from the field value.
     *
     * @param mixed $contentId
     * @param mixed $fieldId
     * @param mixed $versionId
     * @param string $tag
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
     * Cleans up the file name for uploading.
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

    /**
     * Performs the search for the available remote resources.
     *
     * @param $query
     * @param $offset
     * @param $limit
     * @param $hardLimit
     *
     * @return array
     */
    public function searchResources($query, $offset, $limit, $hardLimit)
    {
        if (empty($query)) {
            $list = $this->provider->listResources($hardLimit);
        } else {
            $list = $this->provider->searchResources($query, $hardLimit);
        }

        return array(
            'hits' => $this->provider->formatBrowseList(array_slice($list, $offset, $limit)),
            'count' => count($list),
        );
    }
}
