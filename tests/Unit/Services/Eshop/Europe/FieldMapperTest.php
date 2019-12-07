<?php

namespace Tests\Unit\Services\Eshop;

use Tests\TestCase;

use App\Services\Eshop\Europe\FieldMapper;

class FieldMapperTest extends TestCase
{
    /**
     * @var FieldMapper
     */
    private $fieldMapper;

    public function setUp(): void
    {
        parent::setUp();
        $this->fieldMapper = new FieldMapper();
    }

    public function tearDown(): void
    {
        parent::tearDown();
        unset($this->fieldMapper);
    }

    public function testVersionField()
    {
        $eshopField = '_version_';
        $dbExpectedField = 'version';
        $dbExpectedType = 'string';

        $this->fieldMapper->setField($eshopField);
        $this->assertTrue($this->fieldMapper->fieldExists());
        $this->assertEquals($dbExpectedField, $this->fieldMapper->getDbFieldName());
        $this->assertEquals($dbExpectedType, $this->fieldMapper->getDbFieldType());
    }

    public function testFakeField()
    {
        $eshopField = 'this_field_definitely_does_not_exist';

        $this->fieldMapper->setField($eshopField);
        $this->assertFalse($this->fieldMapper->fieldExists());
        $this->expectExceptionMessage('Field does not exist');
        $fieldName = $this->fieldMapper->getDbFieldName();
        $fieldType = $this->fieldMapper->getDbFieldType();
    }

    public function testBooleanField()
    {
        $eshopField = 'cloud_saves_b';
        $dbExpectedField = 'cloud_saves_b';
        $dbExpectedType = 'boolean';

        $this->fieldMapper->setField($eshopField);
        $this->assertTrue($this->fieldMapper->fieldExists());
        $this->assertEquals($dbExpectedField, $this->fieldMapper->getDbFieldName());
        $this->assertEquals($dbExpectedType, $this->fieldMapper->getDbFieldType());
    }

    public function testJsonField()
    {
        $eshopField = 'compatible_controller';
        $dbExpectedField = 'compatible_controller';
        $dbExpectedType = 'json';

        $this->fieldMapper->setField($eshopField);
        $this->assertTrue($this->fieldMapper->fieldExists());
        $this->assertEquals($dbExpectedField, $this->fieldMapper->getDbFieldName());
        $this->assertEquals($dbExpectedType, $this->fieldMapper->getDbFieldType());
    }

    public function testDefaultField()
    {
        $eshopField = 'developer';
        $dbExpectedField = 'developer';
        $dbExpectedType = 'string';

        $this->fieldMapper->setField($eshopField);
        $this->assertTrue($this->fieldMapper->fieldExists());
        $this->assertEquals($dbExpectedField, $this->fieldMapper->getDbFieldName());
        $this->assertEquals($dbExpectedType, $this->fieldMapper->getDbFieldType());
    }
}