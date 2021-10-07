<?php

declare(strict_types=1);

namespace Netgen\Bundle\RemoteMediaBundle\Tests\Core\Persistence\Legacy\Content\FieldValue\Converter;

use eZ\Publish\Core\Persistence\Legacy\Content\FieldValue\Converter;
use eZ\Publish\Core\Persistence\Legacy\Content\StorageFieldDefinition;
use eZ\Publish\Core\Persistence\Legacy\Content\StorageFieldValue;
use eZ\Publish\SPI\Persistence\Content\FieldValue;
use eZ\Publish\SPI\Persistence\Content\Type\FieldDefinition;
use Netgen\Bundle\RemoteMediaBundle\Core\Persistence\Legacy\Content\FieldValue\Converter\RemoteMediaConverter;
use PHPUnit\Framework\TestCase;
use function json_decode;
use function json_encode;

class RemoteMediaConverterTest extends TestCase
{
    /**
     * @var RemoteMediaConverter
     */
    protected $converter;

    protected function setUp()
    {
        $this->converter = new RemoteMediaConverter();
    }

    public function testInstanceOfConverter()
    {
        self::assertInstanceOf(Converter::class, $this->converter);
    }

    public function testGetIndexColumn()
    {
        self::assertEquals('data_text', $this->converter->getIndexColumn());
    }

    public function testCreate()
    {
        self::assertEquals($this->converter, RemoteMediaConverter::create());
    }

    public function testToStorageValue()
    {
        $fieldValue = new FieldValue(
            [
                'data' => 'data',
            ]
        );

        $storageFieldValue = new StorageFieldValue();

        $this->converter->toStorageValue($fieldValue, $storageFieldValue);
        self::assertEquals($storageFieldValue->dataText, json_encode($fieldValue->data));
    }

    public function testToFieldValue()
    {
        $storageFieldValue = new StorageFieldValue(
            [
                'dataText' => 'data',
            ]
        );

        $fieldValue = new FieldValue();

        $this->converter->toFieldValue($storageFieldValue, $fieldValue);
        self::assertEquals($fieldValue->data, json_decode($storageFieldValue->dataText, true));
    }

    public function testToStorageFieldDefinition()
    {
        $fieldDefinition = new FieldDefinition();
        $storageFieldDefinition = new StorageFieldDefinition();

        self::assertNull($this->converter->toStorageFieldDefinition($fieldDefinition, $storageFieldDefinition));
    }

    public function testToFieldDefinition()
    {
        $storageFieldDefinition = new StorageFieldDefinition();
        $fieldDefinition = new FieldDefinition();

        self::assertNull($this->converter->toFieldDefinition($storageFieldDefinition, $fieldDefinition));
    }
}
