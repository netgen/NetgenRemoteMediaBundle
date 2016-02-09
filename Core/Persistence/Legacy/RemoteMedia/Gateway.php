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
     *
     * @throws \eZ\Publish\Core\Base\Exceptions\NotFoundException
     */
    abstract public function loadField($fieldId, $versionId);

    /**
     * Updates an existing tag
     *
     * @param \eZ\Publish\Core\Persistence\Legacy\Content\StorageFieldValue $storageFieldValue
     * @param mixed $fieldId
     * @param mixed versionId
     */
    abstract public function updateField($storageFieldValue, $fieldId, $versionId);

    /**
     * Returns a row from the database containing field definition information
     *
     * @param $fieldDefinitionId
     *
     * @return array
     *
     * @throws \eZ\Publish\Core\Base\Exceptions\NotFoundException
     */
    abstract public function loadFieldDefinition($fieldDefinitionId);
}
