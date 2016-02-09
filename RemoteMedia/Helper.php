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

    public function __construct(Handler $handler)
    {
        $this->dbHandler = $handler;
    }

    /**
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
     * @param Field $field
     *
     * @return mixed
     */
    public function loadFieldSettingsBySPIField(Field $field)
    {
        return $this->dbHandler->loadFieldSettings($field->fieldDefinitionId);
    }

    /**
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
}
