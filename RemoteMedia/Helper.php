<?php

namespace Netgen\Bundle\RemoteMediaBundle\RemoteMedia;

use eZ\Publish\SPI\Persistence\Content\Field;
use Netgen\Bundle\RemoteMediaBundle\Core\FieldType\RemoteMedia\Value;
use Netgen\Bundle\RemoteMediaBundle\Core\Persistence\Legacy\RemoteMedia\Handler;

class Helper
{
    /**
     * @var \Netgen\Bundle\RemoteMediaBundle\Core\Persistence\Legacy\RemoteMedia\Handler
     */
    protected $dbHandler;

    /**
     * @var \Netgen\Bundle\RemoteMediaBundle\RemoteMedia\RemoteMediaProviderInterface
     */
    protected $provider;

    public function __construct(Handler $handler, RemoteMediaProviderInterface $provider)
    {
        $this->dbHandler = $handler;
        $this->provider = $provider;
    }

    /**
     * Loads field from the database
     *
     * @param $fieldId
     * @param $version
     *
     * @return \eZ\Publish\SPI\Persistence\Content\Field
     */
    public function loadField($fieldId, $versionId)
    {
        return $this->dbHandler->load($fieldId, $versionId);
    }

    /**
     * Loads field settings from the database
     *
     * @param $fieldId
     * @param $versionId
     *
     * @return mixed
     */
    public function loadFieldSettings($fieldId, $versionId)
    {
        $field = $this->loadField($field, $versionId);

        return $this->dbHandler->loadFieldSettings($field->fieldDefinitionId);
    }

    /**
     * Loads field settings for the provided field
     *
     * @param Field $field
     *
     * @return mixed
     */
    public function loadFieldSettingsBySPIField(Field $field)
    {
        return $this->dbHandler->loadFieldSettings($field->fieldDefinitionId);
    }

    /**
     * Loads available formats for the provided field
     *
     * @param Field $field
     *
     * @return array
     */
    public function loadSPIFieldAvailableFormats(Field $field)
    {
        $fieldSettings = $this->loadFieldSettingsBySPIField($field);

        return !empty($fieldSettings['formats']) ? $fieldSettings['formats'] : array();
    }

    /**
     * Updates the field in the database with the provided value
     *
     * @param $value
     * @param $fieldId
     * @param $contentVersionId
     *
     * @return Field
     */
    public function updateField($value, $fieldId, $contentVersionId)
    {
        return $this->dbHandler->update($value, $fieldId, $contentVersionId);
    }

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

        return $this->provider->upload($fileUri, $options);
    }

    protected function filenameCleanUp($fileName)
    {
        $clean = preg_replace("/[^\p{L}|\p{N}]+/u", '_', $fileName);
        $cleanFileName = preg_replace("/[\p{Z}]{2,}/u", '_', $clean);

        return rtrim($cleanFileName, '_');
    }
}
