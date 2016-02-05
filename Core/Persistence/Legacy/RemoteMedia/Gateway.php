<?php

namespace Netgen\Bundle\RemoteMediaBundle\Core\Persistence\Legacy\RemoteMedia;

abstract class Gateway
{
    /**
     * Returns an row from the database containing field data
     *
     * @param mixed $fieldId
     * @param mixed $versionId
     *
     * @return array
     */
    abstract public function loadField($fieldId, $versionId);

    /**
     * Updates an existing tag
     *
     * @param \eZ\Publish\Core\Persistence\Legacy\Content\StorageFieldValue $storageFieldValue
     * @param mixed $fieldId
     * @param mixed $versionId
     */
    abstract public function updateField($storageFieldValue, $fieldId, $versionId);

    abstract public function loadFieldDefinition($fieldDefinitionId);
}
