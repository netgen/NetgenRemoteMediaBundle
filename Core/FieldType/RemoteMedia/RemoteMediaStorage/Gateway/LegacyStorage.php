<?php

namespace Netgen\Bundle\RemoteMediaBundle\Core\FieldType\RemoteMedia\RemoteMediaStorage\Gateway;

use Netgen\Bundle\RemoteMediaBundle\Core\FieldType\RemoteMedia\RemoteMediaStorage\Gateway;
use eZ\Publish\SPI\Persistence\Content\VersionInfo;
use eZ\Publish\Core\Persistence\Database\DatabaseHandler;
use RuntimeException;
use PDO;

class LegacyStorage extends Gateway
{
    /**
     * Connection
     *
     * @var \eZ\Publish\Core\Persistence\Database\DatabaseHandler
     */
    protected $connection;

    /**
     * Sets the data storage connection to use
     *
     * @throws \RuntimeException if $connection is not an instance of
     *         {@link \eZ\Publish\Core\Persistence\Database\DatabaseHandler}
     *
     * @param \eZ\Publish\Core\Persistence\Database\DatabaseHandler $connection
     */
    public function setConnection($connection)
    {
        // This obviously violates the Liskov substitution Principle, but with
        // the given class design there is no sane other option. Actually the
        // dbHandler *should* be passed to the constructor, and there should
        // not be the need to post-inject it.
        if (!$connection instanceof DatabaseHandler) {
            throw new RuntimeException("Invalid connection passed");
        }
        $this->connection = $connection;
    }

    /**
     * Returns the active connection
     *
     * @throws \RuntimeException if no connection has been set, yet.
     *
     * @return \eZ\Publish\Core\Persistence\Database\DatabaseHandler
     */
    protected function getConnection()
    {
        if ($this->connection === null) {
            throw new RuntimeException("Missing database connection.");
        }
        return $this->connection;
    }

    /**
     * Stores the data in the database based on the given field data
     *
     * @param $fieldId
     * @param $resourceId
     * @param $contentId
     * @param $providerIdentifier
     * @param $version
     */
    public function storeFieldData($fieldId, $resourceId, $contentId, $providerIdentifier, $version)
    {
        $connection = $this->getConnection();

        $selectQuery = $connection->createSelectQuery();
        $selectQuery
            ->selectDistinct($connection->quoteColumn("resource_id"))
            ->from($connection->quoteTable("ngremotemedia_field_link"))
            ->where(
                $selectQuery->expr->eq(
                    $connection->quoteColumn("field_id"),
                    $selectQuery->bindValue($fieldId, null, PDO::PARAM_INT)
                ),
                $selectQuery->expr->eq(
                    $connection->quoteColumn("version"),
                    $selectQuery->bindValue($version, null, PDO::PARAM_INT)
                ),
                $selectQuery->expr->eq(
                    $connection->quoteColumn("provider"),
                    $selectQuery->bindValue($providerIdentifier, null, PDO::PARAM_STR)
                )
            );
        $statement = $selectQuery->prepare();
        $statement->execute();
        $rows = $statement->fetchAll(PDO::FETCH_ASSOC);

        if (count($rows) > 0) {
            $updateQuery = $connection->createUpdateQuery();
            $updateQuery
                ->update("ngremotemedia_field_link")
                ->set(
                    $connection->quoteColumn("resource_id"),
                    $updateQuery->bindValue($resourceId, null, PDO::PARAM_STR)
                )
                ->where(
                    $updateQuery->expr->eq(
                        $connection->quoteColumn("field_id"),
                        $updateQuery->bindValue($fieldId, null, PDO::PARAM_INT)
                    ),
                    $updateQuery->expr->eq(
                        $connection->quoteColumn("version"),
                        $updateQuery->bindValue($version, null, PDO::PARAM_INT)
                    )
                );
            $updateQuery->prepare()->execute();
        } else {
            $insertQuery = $connection->createInsertQuery();
            $insertQuery
                ->insertInto($connection->quoteTable("ngremotemedia_field_link"))
                ->set(
                    $connection->quoteColumn("contentobject_id"),
                    $insertQuery->bindValue($contentId, null, PDO::PARAM_INT)
                )->set(
                    $connection->quoteColumn("field_id"),
                    $insertQuery->bindValue($fieldId, null, PDO::PARAM_INT)
                )->set(
                    $connection->quoteColumn("version"),
                    $insertQuery->bindValue($version, null, PDO::PARAM_INT)
                )->set(
                    $connection->quoteColumn("resource_id"),
                    $insertQuery->bindValue($resourceId, null, PDO::PARAM_STR)
                )->set(
                    $connection->quoteColumn("provider"),
                    $insertQuery->bindValue($providerIdentifier, null, PDO::PARAM_STR)
                );
            $insertQuery->prepare()->execute();
        }
    }

    /**
     * Gets the resource ID stored in the field
     *
     * @param \eZ\Publish\SPI\Persistence\Content\VersionInfo $versionInfo
     *
     * @return int product ID
     */
    /*public function getFieldData(VersionInfo $versionInfo)
    {
        //return $this->loadFieldData($versionInfo->contentInfo->id);
    }*/

    /**
     * Deletes field data for content id identified by $versionInfo.
     *
     * @todo: finish and test this method
     *
     * @param \eZ\Publish\SPI\Persistence\Content\VersionInfo $versionInfo
     * @param array $fieldIds
     */
    public function deleteFieldData($fieldId, $resourceId, $contentId, $providerIdentifier, $version)
    {
        $connection = $this->getConnection();
        $contentId = $versionInfo->contentInfo->id;
        $query = $connection->createDeleteQuery();
        $query
            ->deleteFrom($connection->quoteTable("ngremotemedia_field_link"))
            ->where(
                $query->expr->eq(
                    $connection->quoteColumn("field_id"),
                    $query->bindValue($contentId, null, PDO::PARAM_INT)
                ),
                $query->expr->eq(
                    $connection->quoteColumn("version"),
                    $query->bindValue($version, null, PDO::PARAM_INT)
                ),
                $query->expr->eq(
                    $connection->quoteColumn("provider"),
                    $query->bindValue($providerIdentifier, null, PDO::PARAM_STR)
                )
            );
        $query->prepare()->execute();
    }

    public function remoteResourceConnected($resourceId)
    {
        $connection = $this->getConnection();
        $selectQuery = $connection->createSelectQuery();
        $selectQuery
            ->selectDistinct($connection->quoteColumn("resource_id"))
            ->from($connection->quoteTable("ngremotemedia_field_link"))
            ->where(
                $selectQuery->expr->eq(
                    $connection->quoteColumn("resource_id"),
                    $selectQuery->bindValue($resourceId, null, PDO::PARAM_STR)
                )
            );
        $statement = $selectQuery->prepare();
        $statement->execute();
        $rows = $statement->fetchAll(PDO::FETCH_ASSOC);

        if (count($rows) > 0) {
            return true;
        }

        return false;
    }
}
