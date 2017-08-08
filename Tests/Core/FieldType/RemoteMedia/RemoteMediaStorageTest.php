<?php

namespace Netgen\Bundle\RemoteMediaBundle\Tests\Core\FieldType\RemoteMedia;

use eZ\Publish\API\Repository\ContentService;
use eZ\Publish\API\Repository\FieldTypeService;
use eZ\Publish\API\Repository\Values\Content\ContentInfo;
use eZ\Publish\Core\FieldType\StorageGateway;
use eZ\Publish\Core\Repository\Values\Content\Content;
use Netgen\Bundle\RemoteMediaBundle\Core\FieldType\RemoteMedia\RemoteMediaStorage;
use eZ\Publish\SPI\Persistence\Content\Field;
use eZ\Publish\SPI\Persistence\Content\FieldValue;
use eZ\Publish\SPI\Persistence\Content\VersionInfo;
use eZ\Publish\API\Repository\Values\Content\Field as ContentField;
use Netgen\Bundle\RemoteMediaBundle\Core\FieldType\RemoteMedia\Type;
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
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $fieldTypeService;

    /**
     * @var VersionInfo
     */
    protected $versionInfo;

    /**
     * @var array
     */
    protected $context;

    public function setUp()
    {
        $this->gateway = $this->createMock(RemoteMediaStorage\Gateway\LegacyStorage::class);

        $this->contentService = $this->createMock(ContentService::class);
        $this->remoteMediaProvider = $this->createMock(RemoteMediaProvider::class);
        $this->fieldTypeService = $this->createMock(FieldTypeService::class);

        $gateways = array('ngremotemedia' => $this->gateway);

        $this->storage = new RemoteMediaStorage($this->contentService, $this->remoteMediaProvider, $this->fieldTypeService, $gateways);

        $this->versionInfo = new VersionInfo();
        $this->versionInfo->contentInfo = new ContentInfo();

        $connection = $this->getMockForAbstractClass(StorageGateway::class);
        $this->context = array('identifier' => 'ngremotemedia', 'connection' => $connection);
    }

    public function testSetDeleteUnused()
    {
        $this->assertNull($this->storage->setDeleteUnused(false));
    }

    public function testStoreFieldDataWithValue()
    {
        $value = new Value(array('resourceId' => 'test'));

        $field = new Field(
            array(
                'id' => 'some_id',
                'value' => new FieldValue(
                    array(
                        'externalData' => $value,
                    )
                ),
            )
        );

        $this->remoteMediaProvider->expects($this->once())
            ->method('getIdentifier');

        $this->fieldTypeService->expects($this->once())
            ->method('getFieldType')
            ->will($this->returnValue(new Type()));

        $this->gateway->expects($this->once())
            ->method('storeFieldData');

        $this->assertNotTrue($this->storage->storeFieldData($this->versionInfo, $field, $this->context));
    }

    public function testStoreFieldDataWithValidArray()
    {
        $field = new Field(
            array(
                'id' => 'some_id',
                'value' => new FieldValue(
                    array(
                        'externalData' => array(
                            'input_uri' => 'test/path/image.jpg',
                            'alt_text' => 'Test alt text',
                            'caption' => 'Test caption',
                            'variations' => array(),
                        ),
                    )
                ),
            )
        );

        $this->remoteMediaProvider->expects($this->once())
            ->method('upload')
            ->will($this->returnValue(new Value()));

        $this->remoteMediaProvider->expects($this->once())
            ->method('getIdentifier');

        $this->gateway->expects($this->once())
            ->method('storeFieldData');

        $this->assertTrue($this->storage->storeFieldData($this->versionInfo, $field, $this->context));
    }

    public function testStoreFieldDataWithEmptyArray()
    {
        $field = new Field(
            array(
                'id' => 'some_id',
                'value' => new FieldValue(
                    array(
                        'externalData' => array(),
                    )
                ),
            )
        );

        $this->assertNotTrue($this->storage->storeFieldData($this->versionInfo, $field, $this->context));
    }

    public function testStoreFieldDataWithInvalidValue()
    {
        $field = new Field(
            array(
                'id' => 'some_id',
                'value' => new FieldValue(
                    array(
                        'externalData' => 'some_value',
                    )
                ),
            )
        );

        $this->assertNotTrue($this->storage->storeFieldData($this->versionInfo, $field, $this->context));
    }

    public function testGetFieldData()
    {
        $field = new Field(
            array(
                'id' => 'some_id',
                'value' => new FieldValue(
                    array(
                        'externalData' => 'some_value',
                    )
                ),
            )
        );

        $this->assertEmpty($this->storage->getFieldData($this->versionInfo, $field, $this->context));
    }

    public function testDeleteFieldDataWithDeleteUnused()
    {
        $this->storage->setDeleteUnused(true);

        $fieldsIds = array('some_field');

        $field1 = new ContentField(
            array(
                'id' => 'some_field',
                'value' => new FieldValue(
                    array(
                        'externalData' => 'some_value',
                    )
                ),
            )
        );

        $field2 = new ContentField(
            array(
                'id' => 'some_field2',
                'value' => new FieldValue(
                    array(
                        'externalData' => 'some_value',
                    )
                ),
            )
        );

        $content = new Content(
            array(
                'internalFields' => array($field1, $field2),
                'versionInfo' => new VersionInfo(
                    array(
                        'contentInfo' => new ContentInfo(),
                    )
                ),
            )
        );

        $this->contentService->expects($this->once())
            ->method('loadContent')
            ->will($this->returnValue($content));

        $this->gateway->expects($this->once())
            ->method('loadFromTable')
            ->will($this->returnValue(array('some_field')));

        $this->gateway->expects($this->once())
            ->method('deleteFieldData');

        $this->gateway->expects($this->once())
            ->method('remoteResourceConnected')
            ->will($this->returnValue(false));

        $this->remoteMediaProvider->expects($this->once())
            ->method('deleteResource');

        $this->storage->deleteFieldData($this->versionInfo, $fieldsIds, $this->context);
    }

    public function testDeleteFieldDataWithoutDeleteUnused()
    {
        $this->storage->setDeleteUnused(false);

        $fieldsIds = array('some_field');

        $field1 = new ContentField(
            array(
                'id' => 'some_field',
                'value' => new FieldValue(
                    array(
                        'externalData' => 'some_value',
                    )
                ),
            )
        );

        $field2 = new ContentField(
            array(
                'id' => 'some_field2',
                'value' => new FieldValue(
                    array(
                        'externalData' => 'some_value',
                    )
                ),
            )
        );

        $content = new Content(
            array(
                'internalFields' => array($field1, $field2),
                'versionInfo' => new VersionInfo(
                    array(
                        'contentInfo' => new ContentInfo(),
                    )
                ),
            )
        );

        $this->contentService->expects($this->once())
            ->method('loadContent')
            ->will($this->returnValue($content));

        $this->gateway->expects($this->once())
            ->method('deleteFieldData');

        $this->storage->deleteFieldData($this->versionInfo, $fieldsIds, $this->context);
    }

    public function testHasFieldDataShouldReturnTrue()
    {
        $this->assertTrue($this->storage->hasFieldData());
    }

    public function testGetIndexDataShouldReturnFalse()
    {
        $versionInfo = $this->getMockBuilder(VersionInfo::class)
            ->disableOriginalConstructor()
            ->getMock();

        $field = $this->getMockBuilder(Field::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->assertFalse($this->storage->getIndexData($versionInfo, $field, array()));
    }

    public function testCopyLegacyField()
    {
        $value = new Value(array('resourceId' => 'test'));

        $field = new Field(
            array(
                'id' => 'some_field',
                'value' => new FieldValue(
                    array(
                        'externalData' => $value,
                    )
                ),
            )
        );

        $originalField = new Field(
            array(
                'id' => 'some_field2',
                'value' => new FieldValue(
                    array(
                        'externalData' => 'some_value',
                    )
                ),
            )
        );

        $this->fieldTypeService->expects($this->once())
            ->method('getFieldType')
            ->will($this->returnValue(new Type()));

        $this->assertNotTrue($this->storage->copyLegacyField($this->versionInfo, $field, $originalField, $this->context));
    }
}
