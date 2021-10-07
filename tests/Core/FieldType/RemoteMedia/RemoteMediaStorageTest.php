<?php

declare(strict_types=1);

namespace Netgen\Bundle\RemoteMediaBundle\Tests\Core\FieldType\RemoteMedia;

use eZ\Publish\API\Repository\ContentService;
use eZ\Publish\API\Repository\Values\Content\ContentInfo;
use eZ\Publish\API\Repository\Values\Content\Field as ContentField;
use eZ\Publish\Core\FieldType\StorageGateway;
use eZ\Publish\Core\Repository\Values\Content\Content;
use eZ\Publish\SPI\Persistence\Content\Field;
use eZ\Publish\SPI\Persistence\Content\FieldValue;
use eZ\Publish\SPI\Persistence\Content\VersionInfo;
use Netgen\Bundle\RemoteMediaBundle\Core\FieldType\RemoteMedia\RemoteMediaStorage;
use Netgen\Bundle\RemoteMediaBundle\Core\FieldType\RemoteMedia\Value;
use Netgen\Bundle\RemoteMediaBundle\RemoteMedia\RemoteMediaProvider;
use PHPUnit\Framework\TestCase;

class RemoteMediaStorageTest extends TestCase
{
    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $gateway;

    /**
     * @var RemoteMediaStorage
     */
    protected $storage;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $contentService;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $remoteMediaProvider;
    /**
     * @var VersionInfo
     */
    protected $versionInfo;

    /**
     * @var array
     */
    protected $context;

    protected function setUp()
    {
        $this->gateway = $this->createMock(RemoteMediaStorage\Gateway\LegacyStorage::class);

        $this->contentService = $this->createMock(ContentService::class);
        $this->remoteMediaProvider = $this->createMock(RemoteMediaProvider::class);

        $gateways = ['ngremotemedia' => $this->gateway];

        $this->storage = new RemoteMediaStorage($this->contentService, $this->remoteMediaProvider, $gateways);

        $this->versionInfo = new VersionInfo();
        $this->versionInfo->contentInfo = new ContentInfo();

        $connection = $this->getMockForAbstractClass(StorageGateway::class);
        $this->context = ['identifier' => 'ngremotemedia', 'connection' => $connection];
    }

    public function testSetDeleteUnused()
    {
        self::assertNull($this->storage->setDeleteUnused(false));
    }

    public function testStoreFieldDataWithValue()
    {
        $value = new Value(['resourceId' => 'test']);

        $field = new Field(
            [
                'id' => 'some_id',
                'value' => new FieldValue(
                    [
                        'externalData' => $value,
                    ]
                ),
            ]
        );

        $this->remoteMediaProvider->expects(self::once())
            ->method('getIdentifier');

        $this->gateway->expects(self::once())
            ->method('storeFieldData');

        self::assertNotTrue($this->storage->storeFieldData($this->versionInfo, $field, $this->context));
    }

    public function testStoreFieldDataWithValidArray()
    {
        $field = new Field(
            [
                'id' => 'some_id',
                'value' => new FieldValue(
                    [
                        'externalData' => [
                            'input_uri' => 'test/path/image.jpg',
                            'alt_text' => 'Test alt text',
                            'caption' => 'Test caption',
                            'variations' => [],
                        ],
                    ]
                ),
            ]
        );

        $this->remoteMediaProvider->expects(self::once())
            ->method('upload')
            ->willReturn(new Value());

        $this->remoteMediaProvider->expects(self::once())
            ->method('getIdentifier');

        $this->gateway->expects(self::once())
            ->method('storeFieldData');

        self::assertTrue($this->storage->storeFieldData($this->versionInfo, $field, $this->context));
    }

    public function testStoreFieldDataWithEmptyArray()
    {
        $field = new Field(
            [
                'id' => 'some_id',
                'value' => new FieldValue(
                    [
                        'externalData' => [],
                    ]
                ),
            ]
        );

        self::assertNotTrue($this->storage->storeFieldData($this->versionInfo, $field, $this->context));
    }

    public function testStoreFieldDataWithInvalidValue()
    {
        $field = new Field(
            [
                'id' => 'some_id',
                'value' => new FieldValue(
                    [
                        'externalData' => 'some_value',
                    ]
                ),
            ]
        );

        self::assertNotTrue($this->storage->storeFieldData($this->versionInfo, $field, $this->context));
    }

    public function testGetFieldData()
    {
        $field = new Field(
            [
                'id' => 'some_id',
                'value' => new FieldValue(
                    [
                        'externalData' => 'some_value',
                    ]
                ),
            ]
        );

        self::assertEmpty($this->storage->getFieldData($this->versionInfo, $field, $this->context));
    }

    public function testDeleteFieldDataWithDeleteUnused()
    {
        $this->storage->setDeleteUnused(true);

        $fieldsIds = ['some_field'];

        $field1 = new ContentField(
            [
                'id' => 'some_field',
                'value' => new FieldValue(
                    [
                        'externalData' => 'some_value',
                    ]
                ),
            ]
        );

        $field2 = new ContentField(
            [
                'id' => 'some_field2',
                'value' => new FieldValue(
                    [
                        'externalData' => 'some_value',
                    ]
                ),
            ]
        );

        $content = new Content(
            [
                'internalFields' => [$field1, $field2],
                'versionInfo' => new VersionInfo(
                    [
                        'contentInfo' => new ContentInfo(),
                    ]
                ),
            ]
        );

        $this->contentService->expects(self::once())
            ->method('loadContent')
            ->willReturn($content);

        $this->gateway->expects(self::once())
            ->method('loadFromTable')
            ->willReturn(['some_field']);

        $this->gateway->expects(self::once())
            ->method('deleteFieldData');

        $this->gateway->expects(self::once())
            ->method('remoteResourceConnected')
            ->willReturn(false);

        $this->remoteMediaProvider->expects(self::once())
            ->method('deleteResource');

        $this->storage->deleteFieldData($this->versionInfo, $fieldsIds, $this->context);
    }

    public function testDeleteFieldDataWithoutDeleteUnused()
    {
        $this->storage->setDeleteUnused(false);

        $fieldsIds = ['some_field'];

        $field1 = new ContentField(
            [
                'id' => 'some_field',
                'value' => new FieldValue(
                    [
                        'externalData' => 'some_value',
                    ]
                ),
            ]
        );

        $field2 = new ContentField(
            [
                'id' => 'some_field2',
                'value' => new FieldValue(
                    [
                        'externalData' => 'some_value',
                    ]
                ),
            ]
        );

        $content = new Content(
            [
                'internalFields' => [$field1, $field2],
                'versionInfo' => new VersionInfo(
                    [
                        'contentInfo' => new ContentInfo(),
                    ]
                ),
            ]
        );

        $this->contentService->expects(self::once())
            ->method('loadContent')
            ->willReturn($content);

        $this->gateway->expects(self::once())
            ->method('deleteFieldData');

        $this->storage->deleteFieldData($this->versionInfo, $fieldsIds, $this->context);
    }

    public function testHasFieldDataShouldReturnTrue()
    {
        self::assertTrue($this->storage->hasFieldData());
    }

    public function testGetIndexDataShouldReturnFalse()
    {
        $versionInfo = $this->getMockBuilder(VersionInfo::class)
            ->disableOriginalConstructor()
            ->getMock();

        $field = $this->getMockBuilder(Field::class)
            ->disableOriginalConstructor()
            ->getMock();

        self::assertFalse($this->storage->getIndexData($versionInfo, $field, []));
    }

    public function testCopyLegacyField()
    {
        $value = new Value(['resourceId' => 'test']);

        $field = new Field(
            [
                'id' => 'some_field',
                'value' => new FieldValue(
                    [
                        'externalData' => $value,
                    ]
                ),
            ]
        );

        $originalField = new Field(
            [
                'id' => 'some_field2',
                'value' => new FieldValue(
                    [
                        'externalData' => 'some_value',
                    ]
                ),
            ]
        );

        self::assertNotTrue($this->storage->copyLegacyField($this->versionInfo, $field, $originalField, $this->context));
    }
}
