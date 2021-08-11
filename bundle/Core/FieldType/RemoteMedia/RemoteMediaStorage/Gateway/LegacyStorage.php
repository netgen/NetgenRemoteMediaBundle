<?php

declare(strict_types=1);

namespace Netgen\Bundle\RemoteMediaBundle\Core\FieldType\RemoteMedia\RemoteMediaStorage\Gateway;

use Countable;
use eZ\Publish\Core\Persistence\Database\DatabaseHandler;
use eZ\Publish\SPI\Persistence\Content\VersionInfo;
use Netgen\Bundle\RemoteMediaBundle\Core\FieldType\RemoteMedia\RemoteMediaStorage\Gateway;
use PDO;
use RuntimeException;
use function array_map;
use function count;
use function is_array;

class LegacyStorage extends Gateway
{
    /**
     * Connection.
     *
     * @var \eZ\Publish\Core\Persistence\Database\DatabaseHandler
     */
    protected $connection;

    /**
     * Sets the data storage connection to use.
     *
     * @param \eZ\Publish\Core\Persistence\Database\DatabaseHandler $connection
     *
     * @throws \RuntimeException if $connection is not an instance of
     *                           {@link \eZ\Publish\Core\Persistence\Database\DatabaseHandler}
     */
    public function setConnection($connection)
    {
        // This obviously violates the Liskov substitution Principle, but with
        // the given class design there is no sane other option. Actually the
        // dbHandler *should* be passed to the constructor, and there should
        // not be the need to post-inject it.
        if (!$connection instanceof DatabaseHandler) {
            throw new RuntimeException('Invalid connection passed');
        }
        $this->connection = $connection;
    }

    /**
     * Stores the data in the database based on the given field data.
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
            ->selectDistinct($connection->quoteColumn('resource_id'))
            ->from($connection->quoteTable('ngremotemedia_field_link'))
            ->where(
                $selectQuery->expr->eq(
                    $connection->quoteColumn('field_id'),
                    $selectQuery->bindValue($fieldId, null, PDO::PARAM_INT),
                ),
                $selectQuery->expr->eq(
                    $connection->quoteColumn('version'),
                    $selectQuery->bindValue($version, null, PDO::PARAM_INT),
                ),
            );
        $statement = $selectQuery->prepare();
        $statement->execute();

        $rows = $statement->fetchAll(PDO::FETCH_ASSOC);

        if ((is_array($rows) || $rows instanceof Countable ? count($rows) : 0) > 0) {
            $updateQuery = $connection->createUpdateQuery();
            $updateQuery
                ->update('ngremotemedia_field_link')
                ->set(
                    $connection->quoteColumn('resource_id'),
                    $updateQuery->bindValue($resourceId, null, PDO::PARAM_STR),
                )
                ->where(
                    $updateQuery->expr->eq(
                        $connection->quoteColumn('field_id'),
                        $updateQuery->bindValue($fieldId, null, PDO::PARAM_INT),
                    ),
                    $updateQuery->expr->eq(
                        $connection->quoteColumn('version'),
                        $updateQuery->bindValue($version, null, PDO::PARAM_INT),
                    ),
                );
            $updateQuery->prepare()->execute();
        } else {
            $insertQuery = $connection->createInsertQuery();
            $insertQuery
                ->insertInto($connection->quoteTable('ngremotemedia_field_link'))
                ->set(
                    $connection->quoteColumn('contentobject_id'),
                    $insertQuery->bindValue($contentId, null, PDO::PARAM_INT),
                )->set(
                    $connection->quoteColumn('field_id'),
                    $insertQuery->bindValue($fieldId, null, PDO::PARAM_INT),
                )->set(
                    $connection->quoteColumn('version'),
                    $insertQuery->bindValue($version, null, PDO::PARAM_INT),
                )->set(
                    $connection->quoteColumn('resource_id'),
                    $insertQuery->bindValue($resourceId, null, PDO::PARAM_STR),
                )->set(
                    $connection->quoteColumn('provider'),
                    $insertQuery->bindValue($providerIdentifier, null, PDO::PARAM_STR),
                );
            $insertQuery->prepare()->execute();
        }
    }

    /**
     * Gets the resource ID stored in the field.
     *
     * @param \eZ\Publish\SPI\Persistence\Content\VersionInfo $versionInfo
     * @param mixed $contentId
     * @param mixed $fieldId
     * @param mixed $versionNo
     * @param mixed $providerIdentifier
     *
     * @return int product ID
     */
    /*public function getFieldData(VersionInfo $versionInfo)
    {
        //return $this->loadFieldData($versionInfo->contentInfo->id);
    }*/

    /**
     * Deletes the entry in the link table for the provided field id and version.
     *
     * @param $contentId
     * @param $fieldId
     * @param $versionNo
     * @param $providerIdentifier
     *
     * @return mixed
     */
    public function deleteFieldData($contentId, $fieldId, $versionNo, $providerIdentifier)
    {
        $connection = $this->getConnection();
        $query = $connection->createDeleteQuery();
        $query
            ->deleteFrom($connection->quoteTable('ngremotemedia_field_link'))
            ->where(
                $query->expr->eq(
                    $connection->quoteColumn('field_id'),
                    $query->bindValue($fieldId, null, PDO::PARAM_INT),
                ),
                $query->expr->eq(
                    $connection->quoteColumn('contentobject_id'),
                    $query->bindValue($contentId, null, PDO::PARAM_INT),
                ),
                $query->expr->eq(
                    $connection->quoteColumn('version'),
                    $query->bindValue($versionNo, null, PDO::PARAM_INT),
                ),
                $query->expr->eq(
                    $connection->quoteColumn('provider'),
                    $query->bindValue($providerIdentifier, null, PDO::PARAM_STR),
                ),
            );

        $query->prepare()->execute();
    }

    /**
     * Loads resource id for the provided field id and version.
     *
     * @param $contentId
     * @param $fieldId
     * @param $versionNo
     * @param $providerIdentifier
     *
     * @return array
     */
    public function loadFromTable($contentId, $fieldId, $versionNo, $providerIdentifier)
    {
        $connection = $this->getConnection();
        $selectQuery = $connection->createSelectQuery();

        $selectQuery
            ->selectDistinct($connection->quoteColumn('resource_id'))
            ->from($connection->quoteTable('ngremotemedia_field_link'))
            ->where(
                $selectQuery->expr->eq(
                    $connection->quoteColumn('field_id'),
                    $selectQuery->bindValue($fieldId, null, PDO::PARAM_INT),
                ),
                $selectQuery->expr->eq(
                    $connection->quoteColumn('contentobject_id'),
                    $selectQuery->bindValue($contentId, null, PDO::PARAM_INT),
                ),
                $selectQuery->expr->eq(
                    $connection->quoteColumn('version'),
                    $selectQuery->bindValue($versionNo, null, PDO::PARAM_INT),
                ),
                $selectQuery->expr->eq(
                    $connection->quoteColumn('provider'),
                    $selectQuery->bindValue($providerIdentifier, null, PDO::PARAM_STR),
                ),
            );

        $statement = $selectQuery->prepare();
        $statement->execute();

        $rows = $statement->fetchAll(PDO::FETCH_ASSOC);

        return array_map(
            static function ($item) {
                return $item['resource_id'];
            },
            $rows,
        );
    }

    /**
     * Checks if the remote resource is connected to any content.
     *
     * @param $resourceId
     * @param $providerIdentifier
     *
     * @return bool
     */
    public function remoteResourceConnected($resourceId, $providerIdentifier)
    {
        $connection = $this->getConnection();
        $selectQuery = $connection->createSelectQuery();
        $selectQuery
            ->selectDistinct($connection->quoteColumn('resource_id'))
            ->from($connection->quoteTable('ngremotemedia_field_link'))
            ->where(
                $selectQuery->expr->eq(
                    $connection->quoteColumn('resource_id'),
                    $selectQuery->bindValue($resourceId, null, PDO::PARAM_STR),
                ),
                $selectQuery->expr->eq(
                    $connection->quoteColumn('provider'),
                    $selectQuery->bindValue($providerIdentifier, null, PDO::PARAM_STR),
                ),
            );
        $statement = $selectQuery->prepare();
        $statement->execute();

        $rows = $statement->fetchAll(PDO::FETCH_ASSOC);

        return (is_array($rows) || $rows instanceof Countable ? count($rows) : 0) > 0;
    }

    /**
     * Returns the active connection.
     *
     * @throws \RuntimeException if no connection has been set, yet
     *
     * @return \eZ\Publish\Core\Persistence\Database\DatabaseHandler
     */
    protected function getConnection()
    {
        if ($this->connection === null) {
            throw new RuntimeException('Missing database connection.');
        }

        return $this->connection;
    }
}
