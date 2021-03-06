<?php
namespace Moss\Storage\Model\Definition\Index;

use Moss\Storage\Model\ModelInterface;

class ForeignTest extends \PHPUnit_Framework_TestCase
{


    public function testName()
    {
        $index = new Foreign('foo', ['foo' => 'tfoo', 'bar' => 'tbar'], 'table');
        $this->assertEquals('foo', $index->name());
    }

    public function testType()
    {
        $index = new Foreign('foo', ['foo' => 'tfoo', 'bar' => 'tbar'], 'table');
        $this->assertEquals('foreign', $index->type());
    }

    /**
     * @dataProvider fieldsProvider
     */
    public function testFields($local)
    {
        $index = new Foreign('foo', $local, 'table');
        $this->assertEquals($local, $index->fields());
    }

    public function fieldsProvider() {
        return [
            [['foo' => 'tfoo']],
            [['foo' => 'tfoo', 'bar' => 'tbar']],
            [['foo' => 'tfoo', 'bar' => 'tbar', 'yada' => 'tyada']]
        ];
    }

    /**
     * @expectedException \Moss\Storage\Model\Definition\DefinitionException
     * @expectedExceptionMessage No fields in
     */
    public function testWithoutAnyFields()
    {
        new Foreign('foo', [], 'table');
    }


    public function testHasField()
    {
        $index = new Foreign('foo', ['foo' => 'tfoo', 'bar' => 'tbar'], 'table');
        $this->assertTrue($index->hasField('foo'));
        $this->assertTrue($index->hasField('bar'));
    }

    public function testWithoutField()
    {
        $index = new Foreign('foo', ['foo' => 'tfoo', 'bar' => 'tbar'], 'table');
        $this->assertFalse($index->hasField('yada'));
    }

    public function testIsPrimary()
    {
        $index = new Foreign('foo', ['foo' => 'tfoo', 'bar' => 'tbar'], 'table');
        $this->assertFalse($index->isPrimary());
    }

    public function testIsNotUnique()
    {
        $index = new Foreign('foo', ['foo' => 'tfoo', 'bar' => 'tbar'], 'table');
        $this->assertFalse($index->isUnique());
    }

    public function testIsUnique()
    {
        $index = new Foreign('foo', ['foo' => 'tfoo', 'bar' => 'tbar'], 'table');
        $this->assertFalse($index->isUnique());
    }

    public function testForeignTable() {
        $index = new Foreign('foo', ['foo' => 'tfoo', 'bar' => 'tbar'], 'table');
        $this->assertEquals('table', $index->table());
    }
}
