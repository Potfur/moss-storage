<?php
namespace Moss\Storage\Model;

class ModelTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @expectedException \Moss\Storage\Model\ModelException
     * @expectedExceptionMessage Field must be an instance of FieldInterface
     */
    public function testConstructorWithInvalidFieldInstance()
    {
        new Model('Foo', 'foo', [new \stdClass()]);
    }

    /**
     * @expectedException \Moss\Storage\Model\ModelException
     * @expectedExceptionMessage Index must be an instance of IndexInterface
     */
    public function testConstructorWithInvalidIndexInstance()
    {
        $field = $this->getMock('\Moss\Storage\Model\Definition\FieldInterface');

        new Model('Foo', 'foo', [$field], [new \stdClass()]);
    }

    /**
     * @expectedException \Moss\Storage\Model\ModelException
     * @expectedExceptionMessage Relation must be an instance of RelationInterface
     */
    public function testConstructorWithInvalidRelationInstance()
    {
        $field = $this->getMock('\Moss\Storage\Model\Definition\FieldInterface');
        $field->expects($this->once())->method('name')->will($this->returnValue('foo'));

        $index = $this->getMock('\Moss\Storage\Model\Definition\IndexInterface');
        $index->expects($this->once())->method('fields')->will($this->returnValue(['foo']));

        new Model('Foo', 'foo', [$field], [$index], [new \stdClass()]);
    }

    public function testTable()
    {
        $field = $this->getMock('\Moss\Storage\Model\Definition\FieldInterface');

        $model = new Model('Foo', 'foo', [$field]);
        $this->assertEquals('foo', $model->table());
    }

    public function testEntity()
    {
        $field = $this->getMock('\Moss\Storage\Model\Definition\FieldInterface');

        $model = new Model('\Foo', 'foo', [$field]);
        $this->assertEquals('Foo', $model->entity());
    }

    public function testAlias()
    {
        $field = $this->getMock('\Moss\Storage\Model\Definition\FieldInterface');

        $model = new Model('\Foo', 'foo', [$field]);
        $this->assertEquals('foofoo', $model->alias('foofoo'));
    }

    public function testHasField()
    {
        $field = $this->getMock('\Moss\Storage\Model\Definition\FieldInterface');
        $field->expects($this->once())->method('name')->will($this->returnValue('foo'));

        $model = new Model('Foo', 'foo', [$field]);
        $this->assertTrue($model->hasField('foo'));
    }

    public function testFields()
    {
        $field = $this->getMock('\Moss\Storage\Model\Definition\FieldInterface');
        $field->expects($this->once())->method('name')->will($this->returnValue('foo'));

        $model = new Model('Foo', 'foo', [$field]);
        $this->assertEquals(['foo' => $field], $model->fields());
    }

    public function testField()
    {
        $field = $this->getMock('\Moss\Storage\Model\Definition\FieldInterface');
        $field->expects($this->once())->method('name')->will($this->returnValue('foo'));

        $model = new Model('Foo', 'foo', [$field]);
        $this->assertEquals($field, $model->field('foo'));
    }

    public function testPrimaryFields()
    {
        $foo = $this->getMock('\Moss\Storage\Model\Definition\FieldInterface');
        $foo->expects($this->once())->method('name')->will($this->returnValue('foo'));

        $bar = $this->getMock('\Moss\Storage\Model\Definition\FieldInterface');
        $bar->expects($this->once())->method('name')->will($this->returnValue('bar'));

        $index = $this->getMock('\Moss\Storage\Model\Definition\IndexInterface');
        $index->expects($this->once())->method('isPrimary')->will($this->returnValue(true));
        $index->expects($this->exactly(2))->method('fields')->will($this->returnValue(['foo', 'bar']));

        $model = new Model('Foo', 'foo', [$foo, $bar], [$index]);
        $this->assertEquals([$foo, $bar], $model->primaryFields());
    }

    public function testIndexFields()
    {
        $foo = $this->getMock('\Moss\Storage\Model\Definition\FieldInterface');
        $foo->expects($this->once())->method('name')->will($this->returnValue('foo'));

        $bar = $this->getMock('\Moss\Storage\Model\Definition\FieldInterface');
        $bar->expects($this->once())->method('name')->will($this->returnValue('bar'));

        $yada = $this->getMock('\Moss\Storage\Model\Definition\FieldInterface');
        $yada->expects($this->once())->method('name')->will($this->returnValue('yada'));

        $fooBar = $this->getMock('\Moss\Storage\Model\Definition\IndexInterface');
        $fooBar->expects($this->exactly(1))->method('name')->will($this->returnValue('fooBar'));
        $fooBar->expects($this->exactly(2))->method('fields')->will($this->returnValue(['foo', 'bar']));

        $barYada = $this->getMock('\Moss\Storage\Model\Definition\IndexInterface');
        $barYada->expects($this->exactly(1))->method('name')->will($this->returnValue('barYada'));
        $barYada->expects($this->exactly(2))->method('fields')->will($this->returnValue(['bar', 'yada']));

        $model = new Model('Foo', 'foo', [$foo, $bar, $yada], [$fooBar, $barYada]);
        $this->assertEquals([$foo, $bar, $yada], $model->indexFields());
    }

    public function testHasIndex()
    {
        $field = $this->getMock('\Moss\Storage\Model\Definition\FieldInterface');
        $field->expects($this->once())->method('name')->will($this->returnValue('foo'));

        $index = $this->getMock('\Moss\Storage\Model\Definition\IndexInterface');
        $index->expects($this->once())->method('name')->will($this->returnValue('foo'));
        $index->expects($this->once())->method('fields')->will($this->returnValue(['foo']));

        $model = new Model('Foo', 'foo', [$field], [$index]);
        $this->assertTrue($model->hasIndex('foo'));
    }

    public function testIndexes()
    {
        $field = $this->getMock('\Moss\Storage\Model\Definition\FieldInterface');
        $field->expects($this->once())->method('name')->will($this->returnValue('foo'));

        $index = $this->getMock('\Moss\Storage\Model\Definition\IndexInterface');
        $index->expects($this->once())->method('name')->will($this->returnValue('foo'));
        $index->expects($this->once())->method('fields')->will($this->returnValue(['foo']));

        $model = new Model('Foo', 'foo', [$field], [$index]);
        $this->assertEquals(['foo' => $index], $model->indexes());
    }

    public function testIndex()
    {
        $field = $this->getMock('\Moss\Storage\Model\Definition\FieldInterface');
        $field->expects($this->once())->method('name')->will($this->returnValue('foo'));

        $index = $this->getMock('\Moss\Storage\Model\Definition\IndexInterface');
        $index->expects($this->once())->method('name')->will($this->returnValue('foo'));
        $index->expects($this->once())->method('fields')->will($this->returnValue(['foo']));

        $model = new Model('Foo', 'foo', [$field], [$index]);
        $this->assertEquals($index, $model->index('foo'));
    }

    /**
     * @expectedException \Moss\Storage\Model\ModelException
     * @expectedExceptionMessage Unknown field, field "yada" not found in model "Foo"
     */
    public function testUndefinedIndexField()
    {
        $field = $this->getMock('\Moss\Storage\Model\Definition\FieldInterface');
        $field->expects($this->any())->method('name')->will($this->returnValue('foo'));

        $index = $this->getMock('\Moss\Storage\Model\Definition\IndexInterface');
        $index->expects($this->any())->method('name')->will($this->returnValue('foo'));
        $index->expects($this->any())->method('fields')->will($this->returnValue(['yada']));

        new Model('Foo', 'foo', [$field], [$index]);
    }

    /**
     * @expectedException \Moss\Storage\Model\ModelException
     * @expectedExceptionMessage Unknown index, index "yada" not found in model "Foo"
     */
    public function testUndefinedIndex()
    {
        $field = $this->getMock('\Moss\Storage\Model\Definition\FieldInterface');
        $field->expects($this->once())->method('name')->will($this->returnValue('foo'));

        $model = new Model('Foo', 'foo', [$field]);
        $model->index('yada');
    }

    /**
     * @expectedException \Moss\Storage\Model\ModelException
     * @expectedExceptionMessage Unknown field, field "yada" not found in model "Foo"
     */
    public function testUndefinedRelationKeyField()
    {
        $field = $this->getMock('\Moss\Storage\Model\Definition\FieldInterface');
        $field->expects($this->once())->method('name')->will($this->returnValue('foo'));

        $relation = $this->getMock('\Moss\Storage\Model\Definition\RelationInterface');
        $relation->expects($this->once())->method('keys')->will($this->returnValue(['yada' => 'yada']));

        new Model('Foo', 'foo', [$field], [], [$relation]);
    }

    public function testRelations()
    {
        $field = $this->getMock('\Moss\Storage\Model\Definition\FieldInterface');
        $field->expects($this->once())->method('name')->will($this->returnValue('foo'));

        $relation = $this->getMock('\Moss\Storage\Model\Definition\RelationInterface');
        $relation->expects($this->once())->method('name')->will($this->returnValue('Bar'));
        $relation->expects($this->once())->method('keys')->will($this->returnValue(['foo' => 'foo']));

        $model = new Model('Foo', 'foo', [$field], [], [$relation]);
        $this->assertEquals(['Bar' => $relation], $model->relations());
    }

    public function testHasRelations()
    {
        $field = $this->getMock('\Moss\Storage\Model\Definition\FieldInterface');
        $field->expects($this->once())->method('name')->will($this->returnValue('foo'));

        $relation = $this->getMock('\Moss\Storage\Model\Definition\RelationInterface');
        $relation->expects($this->once())->method('entity')->will($this->returnValue('Bar'));
        $relation->expects($this->once())->method('keys')->will($this->returnValue(['foo' => 'foo']));

        $model = new Model('Foo', 'foo', [$field], [], [$relation]);
        $this->assertTrue($model->hasRelation('Bar'));
    }

    public function testRelation()
    {
        $field = $this->getMock('\Moss\Storage\Model\Definition\FieldInterface');
        $field->expects($this->once())->method('name')->will($this->returnValue('foo'));

        $relation = $this->getMock('\Moss\Storage\Model\Definition\RelationInterface');
        $relation->expects($this->once())->method('entity')->will($this->returnValue('Bar'));
        $relation->expects($this->once())->method('keys')->will($this->returnValue(['foo' => 'foo']));

        $model = new Model('Foo', 'foo', [$field], [], [$relation]);
        $this->assertEquals($relation, $model->relation('Bar'));
    }

    /**
     * @expectedException \Moss\Storage\Model\ModelException
     * @expectedExceptionMessage Unknown relation, relation "Bar" not found in model "Foo"
     */
    public function testUndefinedRelation()
    {
        $field = $this->getMock('\Moss\Storage\Model\Definition\FieldInterface');
        $field->expects($this->once())->method('name')->will($this->returnValue('foo'));

        $model = new Model('Foo', 'foo', [$field]);
        $model->relation('Bar');
    }
}
