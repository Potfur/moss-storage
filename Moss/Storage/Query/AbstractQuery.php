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

use Doctrine\DBAL\Connection;
use Doctrine\DBAL\Query\QueryBuilder;
use Moss\Storage\Model\ModelInterface;


/**
 * Abstract Query
 * Implements basic query methods
 *
 * @package Moss\Storage\Query\OperationTraits
 */
abstract class AbstractQuery
{
    /**
     * @var Connection
     */
    protected $connection;

    /**
     * @var ModelInterface
     */
    protected $model;

    /**
     * @var QueryBuilder
     */
    protected $builder;

    /**
     * Returns connection
     *
     * @return Connection
     */
    public function connection()
    {
        return $this->connection;
    }

    /**
     * Returns model
     *
     * @return ModelInterface
     */
    public function model()
    {
        return $this->model;
    }

    /**
     * Returns query builder instance
     *
     * @return QueryBuilder
     */
    public function builder()
    {
        return $this->builder;
    }

    /**
     * Returns current query string
     *
     * @return string
     */
    public function getSQL()
    {
        return (string) $this->builder->getSQL();
    }

    /**
     * Binds value to unique key and returns it
     *
     * @param string $operation
     * @param string $field
     * @param string $type
     * @param mixed  $value
     *
     * @return string
     */
    protected function bind($operation, $field, $type, $value)
    {
        $key = ':' . implode('_', [$operation, count($this->builder->getParameters()), $field]);

        return $this->builder->createNamedParameter($value, $type, $key);
    }

    /**
     * Removes bound values by their prefix
     * If prefix is null - clears all bound values
     *
     * @param null|string $prefix
     */
    protected function resetBinds($prefix = null)
    {
        if ($prefix === null) {
            $this->builder->setParameters([]);

            return;
        }

        $params = (array) $this->builder->getParameters();
        $types = (array) $this->builder->getParameterTypes();

        foreach (array_keys($params) as $key) {
            if (strpos($key, $prefix) === 1) {
                unset($params[$key], $types[$key]);
            }
        }

        $this->builder->setParameters($params, $types);
    }

    /**
     * Returns array with bound values and their placeholders as keys
     *
     * @return array
     */
    public function binds()
    {
        return $this->builder->getParameters();
    }
}
