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
    public function loadField($fieldId, $version)
    {
        return $this->dbHandler->load($fieldId, $version);
    }

    public function loadFieldSettings($fieldId, $version)
    {
        $field = $this->loadField($field, $version);

        return $this->dbHandler->loadFieldSettings($field->fieldDefinitionId);
    }

    public function loadFieldSettingsBySPIField(Field $field)
    {
        return $this->dbHandler->loadFieldSettings($field->fieldDefinitionId);
    }

    public function loadSPIFieldAvailableFormats(Field $field)
    {
        $fieldSettings = $this->loadFieldSettingsBySPIField($field);

        return !empty($fieldSettings['formats']) ? $fieldSettings['formats'] : array();
    }
}
