<?php

/*
* This file is part of the moss-storage package
*
* (c) Michal Wachowski <wachowski.michal@gmail.com>
*
* For the full copyright and license information, please view the LICENSE
* file that was distributed with this source code.
*/

namespace Moss\Storage\Query;


class StorageTest extends QueryMocks
{
    public function testConnection()
    {
        $dbal = $this->mockDBAL();
        $model = $this->mockModel('\\stdClass', 'table');
        $bag = $this->mockBag([$model]);
        $factory = $this->mockRelFactory();

        $query = new Storage($dbal, $bag, $factory);
        $this->assertSame($dbal, $query->connection());
    }

    /**
     * @dataProvider instanceProvider
     */
    public function testRead()
    {
        $dbal = $this->mockDBAL();
        $model = $this->mockModel('\\stdClass', 'table');
        $bag = $this->mockBag([$model]);
        $factory = $this->mockRelFactory();

        $query = new Storage($dbal, $bag, $factory);
        $this->assertInstanceOf('\Moss\Storage\Query\ReadQueryInterface', $query->read('\\stdClass'));
    }

    /**
     * @dataProvider instanceProvider
     */
    public function testReadOne()
    {
        $dbal = $this->mockDBAL();
        $model = $this->mockModel('\\stdClass', 'table');
        $bag = $this->mockBag([$model]);
        $factory = $this->mockRelFactory();

        $query = new Storage($dbal, $bag, $factory);
        $this->assertInstanceOf('\Moss\Storage\Query\ReadQueryInterface', $query->readOne('\\stdClass'));
    }

    /**
     * @dataProvider instanceProvider
     */
    public function testWrite($entity, $instance)
    {
        $dbal = $this->mockDBAL();
        $model = $this->mockModel('\\stdClass', 'table');
        $bag = $this->mockBag([$model]);
        $factory = $this->mockRelFactory();

        $query = new Storage($dbal, $bag, $factory);
        $this->assertInstanceOf('\Moss\Storage\Query\WriteQueryInterface', $query->write($entity, $instance));
    }

    /**
     * @dataProvider instanceProvider
     */
    public function testInsert($entity, $instance)
    {
        $dbal = $this->mockDBAL();
        $model = $this->mockModel('\\stdClass', 'table');
        $bag = $this->mockBag([$model]);
        $factory = $this->mockRelFactory();

        $query = new Storage($dbal, $bag, $factory);
        $this->assertInstanceOf('\Moss\Storage\Query\InsertQueryInterface', $query->insert($entity, $instance));
    }

    /**
     * @dataProvider instanceProvider
     */
    public function testUpdate($entity, $instance)
    {
        $dbal = $this->mockDBAL();
        $model = $this->mockModel('\\stdClass', 'table');
        $bag = $this->mockBag([$model]);
        $factory = $this->mockRelFactory();

        $query = new Storage($dbal, $bag, $factory);
        $this->assertInstanceOf('\Moss\Storage\Query\UpdateQueryInterface', $query->update($entity, $instance));
    }

    /**
     * @dataProvider instanceProvider
     */
    public function testDelete($entity, $instance)
    {
        $dbal = $this->mockDBAL();
        $model = $this->mockModel('\\stdClass', 'table');
        $bag = $this->mockBag([$model]);
        $factory = $this->mockRelFactory();

        $query = new Storage($dbal, $bag, $factory);
        $this->assertInstanceOf('\Moss\Storage\Query\DeleteQueryInterface', $query->delete($entity, $instance));
    }

    public function instanceProvider()
    {
        return [
            ['\\stdClass', new \stdClass()],
            [new \stdClass(), null],
            ['table', new \stdClass()]
        ];
    }
}