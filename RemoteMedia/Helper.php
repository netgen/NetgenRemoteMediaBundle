<?php

namespace Netgen\Bundle\RemoteMediaBundle\RemoteMedia;

use eZ\Publish\API\Repository\ContentService;
use eZ\Publish\API\Repository\ContentTypeService;
use eZ\Publish\Core\Base\Exceptions\NotFoundException;
use Netgen\Bundle\RemoteMediaBundle\Core\FieldType\RemoteMedia\Value;

/**
 * Class Helper
 * @internal
 */
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
    protected function loadValue($contentId, $fieldId, $versionId = null, $languageCode = null)
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
    protected function updateValue($value, $contentId, $fieldId, $contentVersionId, $languageCode = null)
    {
        $field = $this->loadField($contentId, $fieldId, $contentVersionId, $languageCode);

        $versionInfo = $this->contentService->loadVersionInfoById($contentId, $contentVersionId);

        $contentUpdateStruct = $this->contentService->newContentUpdateStruct();
        $contentUpdateStruct->setField($field->fieldDefIdentifier, $value);
        $contentDraft = $this->contentService->updateContent($versionInfo, $contentUpdateStruct);

        return $value;
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
     * Formats browse list to comply with javascript.
     *
     * @todo: check if can be removed/refractored
     *
     * @param array $list
     *
     * @return array
     */
    protected function formatBrowseList(array $list)
    {
        $listFormatted = array();
        foreach ($list as $hit) {
            $thumbOptions = array();
            $thumbOptions['crop'] = 'fit';
            $thumbOptions['width'] = 160;
            $thumbOptions['height'] = 120;

            $value = Value::createFromCloudinaryResponse($hit);

            $listFormatted[] = array(
                'resourceId' => $hit['public_id'],
                'tags' => $hit['tags'],
                'type' => $hit['resource_type'],
                'filesize' => $hit['bytes'],
                'width' => $hit['width'],
                'height' => $hit['height'],
                'filename' => $hit['public_id'],
                'url' => $this->provider->buildVariation($value, 'admin', $thumbOptions)->url,
            );
        }

        return $listFormatted;
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
            $listByTags = $this->provider->searchResourcesByTag($query);

            $list = array_merge($list, $listByTags);
        }

        return array(
            'hits' => $this->formatBrowseList(array_slice($list, $offset, $limit)),
            'count' => count($list),
        );
    }
}
