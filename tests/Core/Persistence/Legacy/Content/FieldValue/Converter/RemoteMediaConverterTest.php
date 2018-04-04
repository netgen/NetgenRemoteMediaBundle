<?php

namespace Netgen\Bundle\RemoteMediaBundle\Tests\Core\Persistence\Legacy\Content\FieldValue\Converter;

use eZ\Publish\Core\Persistence\Legacy\Content\FieldValue\Converter;
use eZ\Publish\Core\Persistence\Legacy\Content\StorageFieldDefinition;
use eZ\Publish\Core\Persistence\Legacy\Content\StorageFieldValue;
use eZ\Publish\SPI\Persistence\Content\FieldValue;
use eZ\Publish\SPI\Persistence\Content\Type\FieldDefinition;
use Netgen\Bundle\RemoteMediaBundle\Core\Persistence\Legacy\Content\FieldValue\Converter\RemoteMediaConverter;
use PHPUnit\Framework\TestCase;

class RemoteMediaConverterTest extends TestCase
{
    /**
     * @var RemoteMediaConverter
     */
    protected $converter;

    public function setUp()
    {
        $this->converter = new RemoteMediaConverter();
    }

    public function testInstanceOfConverter()
    {
        $this->assertInstanceOf(Converter::class, $this->converter);
    }

    public function testGetIndexColumn()
    {
        $this->assertEquals('data_text', $this->converter->getIndexColumn());
    }

    public function testCreate()
    {
        $this->assertEquals($this->converter, RemoteMediaConverter::create());
    }

    public function testToStorageValue()
    {
        $fieldValue = new FieldValue(
            array(
                'data' => 'data',
            )
        );

        $storageFieldValue = new StorageFieldValue();

        $this->converter->toStorageValue($fieldValue, $storageFieldValue);
        $this->assertEquals($storageFieldValue->dataText, json_encode($fieldValue->data));
    }

    public function testToFieldValue()
    {
        $storageFieldValue = new StorageFieldValue(
            array(
                'dataText' => 'data',
            )
        );

        $fieldValue = new FieldValue();

        $this->converter->toFieldValue($storageFieldValue, $fieldValue);
        $this->assertEquals($fieldValue->data, json_decode($storageFieldValue->dataText, true));
    }

    public function testToStorageFieldDefinition()
    {
        $fieldDefinition = new FieldDefinition();
        $storageFieldDefinition = new StorageFieldDefinition();

        $this->assertNull($this->converter->toStorageFieldDefinition($fieldDefinition, $storageFieldDefinition));
    }

    public function testToFieldDefinition()
    {
        $storageFieldDefinition = new StorageFieldDefinition();
        $fieldDefinition = new FieldDefinition();

        $this->assertNull($this->converter->toFieldDefinition($storageFieldDefinition, $fieldDefinition));
    }
}
