<?php

namespace Netgen\Bundle\RemoteMediaBundle\Core\Persistence\Legacy\RemoteMedia\Gateway;

use Netgen\Bundle\RemoteMediaBundle\Core\FieldType\RemoteMedia\Value;
use Netgen\Bundle\RemoteMediaBundle\Core\Persistence\Legacy\RemoteMedia\Gateway;
use eZ\Publish\Core\Persistence\Database\DatabaseHandler;
use eZ\Publish\Core\Base\Exceptions\NotFoundException;
use PDO;

class DoctrineDatabase extends Gateway
{
    /**
     * Database handler
     *
     * @var \eZ\Publish\Core\Persistence\Database\DatabaseHandler
     */
    protected $handler;

    /**
     * Construct from database handler
     *
     * @param \eZ\Publish\Core\Persistence\Database\DatabaseHandler $handler
     */
    public function __construct(DatabaseHandler $handler)
    {
        $this->handler = $handler;
    }

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
    public function loadField($fieldId, $versionId)
    {
        $query = $this->handler->createSelectQuery();
        $query
            ->select("*")
            ->from($this->handler->quoteTable("ezcontentobject_attribute"))
            ->where(
                $query->expr->eq(
                    $this->handler->quoteColumn("id"),
                    $query->bindValue($fieldId, null, PDO::PARAM_INT)
                ),
                $query->expr->eq(
                    $this->handler->quoteColumn("version"),
                    $query->bindValue($versionId, null, PDO::PARAM_INT)
                )
            )
        ;

        $statement = $query->prepare();
        $statement->execute();

        if ($row = $statement->fetch(PDO::FETCH_ASSOC))
        {
            return $row;
        }

        throw new NotFoundException("field", $fieldId);
    }

    /**
     * Returns row from the database containing field definition for the field with provided id
     *
     * @param mixed $fieldId
     * @param mixed $versionId
     *
     * @return array
     *
     * @throws \eZ\Publish\Core\Base\Exceptions\NotFoundException
     */
    public function loadFieldDefinitionByFieldId($fieldId, $versionId)
    {
        $query = $this->handler->createSelectQuery();
        $query
            ->select("ezcontentclass_attribute.*")
            ->from($this->handler->quoteTable("ezcontentclass_attribute"))
            ->leftJoin(
                $this->handler->quoteTable("ezcontentobject_attribute"),
                $this->handler->quoteColumn("id", "ezcontentclass_attribute"),
                $this->handler->quoteColumn("contentclassattribute_id", "ezcontentobject_attribute")
            )->where(
                $query->expr->eq(
                    $this->handler->quoteColumn("id", "ezcontentobject_attribute"),
                    $query->bindValue($fieldId, null, PDO::PARAM_INT)
                ),
                $query->expr->eq(
                    $this->handler->quoteColumn("version", "ezcontentobject_attribute"),
                    $query->bindValue($versionId, null, PDO::PARAM_INT)
                )
            )
        ;

        $statement = $query->prepare();
        $statement->execute();

        if ($row = $statement->fetch(PDO::FETCH_ASSOC))
        {
            return $row;
        }

        throw new NotFoundException("field definition", $fieldId);
    }


    /**
     * Updates field
     *
     * @param \eZ\Publish\Core\Persistence\Legacy\Content\StorageFieldValue $storageFieldValue
     * @param mixed $fieldId
     * @param mixed versionId
     */
    public function updateField($storageFieldValue, $fieldId, $versionId)
    {
        $query = $this->handler->createUpdateQuery();
        $query
            ->update( $this->handler->quoteTable( "ezcontentobject_attribute" ) )
            ->set(
                $this->handler->quoteColumn( "data_text" ),
                $query->bindValue( $storageFieldValue->dataText, null, PDO::PARAM_STR )
            )->where(
                $query->expr->eq(
                    $this->handler->quoteColumn( "id" ),
                    $query->bindValue( $fieldId, null, PDO::PARAM_INT )
                ),
                $query->expr->eq(
                    $this->handler->quoteColumn( "version" ),
                    $query->bindValue( $versionId, null, PDO::PARAM_INT )
                )
            );

        $query->prepare()->execute();
    }
}
