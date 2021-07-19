<?php

declare(strict_types=1);

namespace Netgen\Bundle\RemoteMediaBundle\Tests\Core\FieldType\RemoteMedia;

use Doctrine\DBAL\Connection;
use Doctrine\DBAL\Statement;
use eZ\Publish\Core\Persistence\Database\DatabaseHandler;
use eZ\Publish\Core\Persistence\Doctrine\ConnectionHandler;
use eZ\Publish\Core\Persistence\Doctrine\DeleteDoctrineQuery;
use eZ\Publish\Core\Persistence\Doctrine\InsertDoctrineQuery;
use eZ\Publish\Core\Persistence\Doctrine\SelectDoctrineQuery;
use eZ\Publish\Core\Persistence\Doctrine\UpdateDoctrineQuery;
use Netgen\Bundle\RemoteMediaBundle\Core\FieldType\RemoteMedia\RemoteMediaStorage\Gateway;
use Netgen\Bundle\RemoteMediaBundle\Core\FieldType\RemoteMedia\RemoteMediaStorage\Gateway\LegacyStorage;
use Netgen\Bundle\RemoteMediaBundle\RemoteMedia\RemoteMediaProvider;
use PHPUnit\Framework\TestCase;

class LegacyStorageTest extends TestCase
{
    /**
     * @var LegacyStorage
     */
    protected $storage;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $connection;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $remoteMediaProvider;

    protected function setUp()
    {
        $this->connection = $this->getMockBuilder(ConnectionHandler::class)
            ->disableOriginalConstructor()
            ->setMethods(['createDeleteQuery', 'quoteColumn', 'createInsertQuery', 'createSelectQuery', 'createUpdateQuery'])
            ->getMock();

        $this->storage = new LegacyStorage();
        $this->remoteMediaProvider = $this->createMock(RemoteMediaProvider::class);
    }

    public function testInstanceOfGateway()
    {
        self::assertInstanceOf(Gateway::class, $this->storage);
    }

    public function testConnectionHandling()
    {
        $handler = $this->getMockForAbstractClass(DatabaseHandler::class);

        $this->storage->setConnection($handler);

        // Avoid detecting risky tests
        self::assertTrue(true);
    }

    /**
     * @expectedException \RuntimeException
     * @expectedExceptionMessage Invalid connection passed
     */
    public function testConnectionHandlingWithInvalidConnection()
    {
        $handler = new \stdClass();

        $this->storage->setConnection($handler);

        // Avoid detecting risky tests
        self::assertTrue(true);
    }

    public function testStoreFieldDataInsertNew()
    {
        $connection = $this->getMockBuilder(Connection::class)
            ->disableOriginalConstructor()
            ->getMock();

        $selectQuery = $this->getMockBuilder(SelectDoctrineQuery::class)
            ->setConstructorArgs([$connection])
            ->setMethods(['prepare'])
            ->getMock();

        $insertQuery = $this->getMockBuilder(InsertDoctrineQuery::class)
            ->setConstructorArgs([$connection])
            ->setMethods(['prepare'])
            ->getMock();

        $statement = $this->getMockBuilder(Statement::class)
            ->disableOriginalConstructor()
            ->setMethods(['execute', 'fetchAll'])
            ->getMock();

        $result = [
            ['resource_id' => 'some_resource_id'],
        ];

        $statement->expects(self::once())
            ->method('fetchAll')
            ->willReturn([]);

        $selectQuery->expects(self::once())
            ->method('prepare')
            ->willReturn($statement);

        $insertQuery->expects(self::once())
            ->method('prepare')
            ->willReturn($statement);

        $statement->expects(self::at(2))
            ->method('execute');

        $this->connection->expects(self::once())
            ->method('createSelectQuery')
            ->willReturn($selectQuery);

        $this->connection->expects(self::once())
            ->method('createInsertQuery')
            ->willReturn($insertQuery);

        $this->storage->setConnection($this->connection);
        $this->storage->storeFieldData('some_id', 'some_field_id', 'some_version_no', 'some_provider_id', 1);
    }

    public function testStoreFieldDataUpdateExisting()
    {
        $connection = $this->getMockBuilder(Connection::class)
            ->disableOriginalConstructor()
            ->getMock();

        $selectQuery = $this->getMockBuilder(SelectDoctrineQuery::class)
            ->setConstructorArgs([$connection])
            ->setMethods(['prepare'])
            ->getMock();

        $updateQuery = $this->getMockBuilder(UpdateDoctrineQuery::class)
            ->setConstructorArgs([$connection])
            ->setMethods(['prepare'])
            ->getMock();

        $statement = $this->getMockBuilder(Statement::class)
            ->disableOriginalConstructor()
            ->setMethods(['execute', 'fetchAll'])
            ->getMock();

        $result = [
            ['resource_id' => 'some_resource_id'],
        ];

        $statement->expects(self::once())
            ->method('fetchAll')
            ->willReturn($result);

        $selectQuery->expects(self::once())
            ->method('prepare')
            ->willReturn($statement);

        $updateQuery->expects(self::once())
            ->method('prepare')
            ->willReturn($statement);

        $statement->expects(self::at(2))
            ->method('execute');

        $this->connection->expects(self::once())
            ->method('createSelectQuery')
            ->willReturn($selectQuery);

        $this->connection->expects(self::once())
            ->method('createUpdateQuery')
            ->willReturn($updateQuery);

        $this->storage->setConnection($this->connection);
        $this->storage->storeFieldData('some_id', 'some_field_id', 'some_version_no', 'some_provider_id', 1);
    }

    public function testDeleteFieldData()
    {
        $connection = $this->getMockBuilder(Connection::class)
            ->disableOriginalConstructor()
            ->getMock();

        $query = $this->getMockBuilder(DeleteDoctrineQuery::class)
            ->setConstructorArgs([$connection])
            ->setMethods(['prepare'])
            ->getMock();

        $statement = $this->getMockBuilder(Statement::class)
            ->disableOriginalConstructor()
            ->setMethods(['execute'])
            ->getMock();

        $query->expects(self::once())
            ->method('prepare')
            ->willReturn($statement);

        $statement->expects(self::once())
            ->method('execute');

        $this->connection->expects(self::once())
            ->method('createDeleteQuery')
            ->willReturn($query);

        $this->storage->setConnection($this->connection);
        $this->storage->deleteFieldData('some_id', 'some_field_id', 'some_version_no', 'some_provider_id', 1);
    }

    public function testLoadFromTable()
    {
        $connection = $this->getMockBuilder(Connection::class)
            ->disableOriginalConstructor()
            ->getMock();

        $query = $this->getMockBuilder(SelectDoctrineQuery::class)
            ->setConstructorArgs([$connection])
            ->setMethods(['prepare'])
            ->getMock();

        $statement = $this->getMockBuilder(Statement::class)
            ->disableOriginalConstructor()
            ->setMethods(['execute', 'fetchAll'])
            ->getMock();

        $this->connection->expects(self::once())
            ->method('createSelectQuery')
            ->willReturn($query);

        $query->expects(self::once())
            ->method('prepare')
            ->willReturn($statement);

        $statement->expects(self::once())
            ->method('execute');

        $result = [
            ['resource_id' => 'some_resource_id'],
        ];

        $statement->expects(self::once())
            ->method('fetchAll')
            ->willReturn($result);

        $this->storage->setConnection($this->connection);
        $this->storage->loadFromTable('some_id', 'some_field_id', 'some_version_no', 'some_provider_id');
    }

    public function testRemoteResourceConnectedWithResults()
    {
        $connection = $this->getMockBuilder(Connection::class)
            ->disableOriginalConstructor()
            ->getMock();

        $query = $this->getMockBuilder(SelectDoctrineQuery::class)
            ->setConstructorArgs([$connection])
            ->setMethods(['prepare'])
            ->getMock();

        $statement = $this->getMockBuilder(Statement::class)
            ->disableOriginalConstructor()
            ->setMethods(['execute', 'fetchAll'])
            ->getMock();

        $this->connection->expects(self::once())
            ->method('createSelectQuery')
            ->willReturn($query);

        $query->expects(self::once())
            ->method('prepare')
            ->willReturn($statement);

        $statement->expects(self::once())
            ->method('execute');

        $result = [
            ['resource_id' => 'some_resource_id'],
        ];

        $statement->expects(self::once())
            ->method('fetchAll')
            ->willReturn($result);

        $this->storage->setConnection($this->connection);
        self::assertTrue($this->storage->remoteResourceConnected('some_resource_id', 'some_provider_identifier'));
    }

    public function testRemoteResourceConnectedWithoutResults()
    {
        $connection = $this->getMockBuilder(Connection::class)
            ->disableOriginalConstructor()
            ->getMock();

        $query = $this->getMockBuilder(SelectDoctrineQuery::class)
            ->setConstructorArgs([$connection])
            ->setMethods(['prepare'])
            ->getMock();

        $statement = $this->getMockBuilder(Statement::class)
            ->disableOriginalConstructor()
            ->setMethods(['execute', 'fetchAll'])
            ->getMock();

        $this->connection->expects(self::once())
            ->method('createSelectQuery')
            ->willReturn($query);

        $query->expects(self::once())
            ->method('prepare')
            ->willReturn($statement);

        $statement->expects(self::once())
            ->method('execute');

        $result = [];

        $statement->expects(self::once())
            ->method('fetchAll')
            ->willReturn($result);

        $this->storage->setConnection($this->connection);
        self::assertNotTrue($this->storage->remoteResourceConnected('some_resource_id', 'some_provider_identifier'));
    }

    /**
     * @expectedException \RuntimeException
     */
    public function testGetConnectionWithNull()
    {
        $this->storage->deleteFieldData('some_id', 'some_field_id', 'some_version_no', 'some_provider_id', 1);
    }
}
