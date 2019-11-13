<?php

declare(strict_types=1);

namespace Netgen\Bundle\RemoteMediaBundle\Tests\Core\FieldType\RemoteMedia;

use eZ\Publish\Core\FieldType\Date\Value as DateValue;
use eZ\Publish\SPI\FieldType\FieldType;
use eZ\Publish\SPI\Persistence\Content\FieldValue;
use Netgen\Bundle\RemoteMediaBundle\Core\FieldType\RemoteMedia\InputValue;
use Netgen\Bundle\RemoteMediaBundle\Core\FieldType\RemoteMedia\Type;
use Netgen\Bundle\RemoteMediaBundle\Core\FieldType\RemoteMedia\Value;
use PHPUnit\Framework\TestCase;

class TypeTest extends TestCase
{
    const PARAMETERS = ['resourceId' => 'test'];

    const INPUT_PARAMETERS = [
        'input_uri' => 'test/path/image.jpg',
        'alt_text' => 'Test alt text',
        'caption' => 'Test caption',
        'variations' => [],
    ];

    /**
     * @var Type
     */
    protected $type;

    protected $value;

    protected $emptyValue;

    protected $inputValue;

    protected $emptyInputValue;

    public function setUp()
    {
        $this->type = new Type();
        $this->value = new Value(self::PARAMETERS);
        $this->emptyValue = new Value();
        $this->inputValue = new InputValue(self::INPUT_PARAMETERS);
        $this->emptyInputValue = new InputValue();
    }

    public function testInstanceOfFieldType()
    {
        $this->assertInstanceOf(FieldType::class, $this->type);
    }

    public function testGetFieldTypeIdentifier()
    {
        $this->assertEquals('ngremotemedia', $this->type->getFieldTypeIdentifier());
    }

    public function testGetNameWithValue()
    {
        $this->assertEquals($this->value->resourceId, $this->type->getName($this->value));
    }

    public function testGetNameWithEmptyValue()
    {
        $this->assertEquals($this->emptyValue->resourceId, $this->type->getName($this->emptyValue));
    }

    public function testGetEmptyValue()
    {
        $this->assertEquals($this->emptyValue, $this->type->getEmptyValue());
    }

    public function testFromHash()
    {
        $this->assertEquals($this->inputValue, $this->type->fromHash(self::INPUT_PARAMETERS));
    }

    public function testFromHashWithEmptyHash()
    {
        $this->assertEquals($this->emptyValue, $this->type->fromHash(''));
    }

    public function testToHash()
    {
        $this->assertEquals((array) $this->value, $this->type->toHash($this->value));
    }

    public function testInputValueToPersistenceValue()
    {
        $fieldValue = new FieldValue(
            [
                'data' => null,
                'externalData' => (array) $this->inputValue,
                'sortKey' => false,
            ]
        );

        $this->assertEquals($fieldValue, $this->type->toPersistenceValue($this->inputValue));
    }

    public function testValueToPersistenceValue()
    {
        $fieldValue = new FieldValue(
            [
                'data' => $this->value,
                'externalData' => $this->value,
                'sortKey' => false,
            ]
        );

        $this->assertEquals($fieldValue, $this->type->toPersistenceValue($this->value));
    }

    public function testInvalidValueToPersistenceValue()
    {
        $spiValue = new DateValue();

        $this->assertNull($this->type->toPersistenceValue($spiValue));
    }

    public function testValueFromPersistenceValue()
    {
        $fieldValue = new FieldValue(
            [
                'data' => $this->value,
            ]
        );

        $this->assertEquals($this->value, $this->type->fromPersistenceValue($fieldValue));
    }

    public function testParametersValueFromPersistenceValue()
    {
        $fieldValue = new FieldValue(
            [
                'data' => self::PARAMETERS,
            ]
        );

        $this->assertEquals($this->value, $this->type->fromPersistenceValue($fieldValue));
    }

    public function testNullValueFromPersistenceValue()
    {
        $fieldValue = new FieldValue(
            [
                'data' => null,
            ]
        );

        $this->assertEquals($this->emptyValue, $this->type->fromPersistenceValue($fieldValue));
    }

    public function testEmptyValueFromPersistenceValue()
    {
        $fieldValue = new FieldValue(
            [
                'data' => $this->emptyValue,
            ]
        );

        $this->assertEquals($this->emptyValue, $this->type->fromPersistenceValue($fieldValue));
    }

    public function testIsEmptyValueWithInputValue()
    {
        $this->assertNotTrue($this->type->isEmptyValue($this->inputValue));
    }

    public function testIsEmptyValueWithValue()
    {
        $this->assertNotTrue($this->type->isEmptyValue($this->value));
    }

    public function testIsEmptyValueWithEmptyInputValue()
    {
        $this->assertTrue($this->type->isEmptyValue($this->emptyInputValue));
    }

    public function testIsEmptyValueWithEmptyValue()
    {
        $this->assertTrue($this->type->isEmptyValue($this->emptyValue));
    }

    public function testIsEmptyValueWithValueWithoutResourceId()
    {
        $spiValue = new Value(['url' => 'test']);

        $this->assertTrue($this->type->isEmptyValue($spiValue));
    }

    public function testIsSearchableShouldAlwaysReturnTrue()
    {
        $this->assertTrue($this->type->isSearchable());
    }

    public function testAcceptValueWithSingle()
    {
        $value = new InputValue(['input_uri' => 'test_uri']);

        $returnedValue = $this->type->acceptValue('test_uri');

        $this->assertEquals($value, $returnedValue);
    }

    public function testAcceptValueWithValidArray()
    {
        $returnedValue = $this->type->acceptValue(self::INPUT_PARAMETERS);

        $this->assertEquals($this->inputValue, $returnedValue);
    }

    /**
     * @expectedException \eZ\Publish\API\Repository\Exceptions\PropertyNotFoundException
     */
    public function testAcceptValueWithInvalidArray()
    {
        $returnedValue = $this->type->acceptValue([1]);

        $this->assertEquals(1, $returnedValue);
    }

    public function testAcceptValueWithInputValueObject()
    {
        $this->assertEquals($this->inputValue, $this->type->acceptValue($this->inputValue));
    }

    public function testAcceptValueWithValueObject()
    {
        $this->assertEquals($this->value, $this->type->acceptValue($this->value));
    }

    /**
     * @expectedException \eZ\Publish\API\Repository\Exceptions\InvalidArgumentException
     */
    public function testAcceptValueWithInvalidStructuredInputValueObject()
    {
        $value = new InputValue(['input_uri' => ['test_uri']]);

        $this->assertEquals($value, $this->type->acceptValue($value));
    }

    /**
     * @expectedException \eZ\Publish\API\Repository\Exceptions\InvalidArgumentException
     */
    public function testAcceptValueWithInvalidStructuredValueObject()
    {
        $value = new Value(['resourceId' => ['test']]);

        $this->assertEquals($value, $this->type->acceptValue($value));
    }

    /**
     * @expectedException \eZ\Publish\Core\Base\Exceptions\InvalidArgumentType
     */
    public function testAcceptValueWithInvalidValueObjectType()
    {
        $value = 1;

        $this->assertEquals($value, $this->type->acceptValue($value));
    }
}
