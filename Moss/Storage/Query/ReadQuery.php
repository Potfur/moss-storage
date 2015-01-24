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
use Moss\Storage\Converter\ConverterInterface;
use Moss\Storage\Model\Definition\FieldInterface;
use Moss\Storage\Model\ModelInterface;
use Moss\Storage\Query\Relation\RelationFactoryInterface;
use Moss\Storage\Query\Relation\RelationInterface;

/**
 * Query used to read data from table
 *
 * @author  Michal Wachowski <wachowski.michal@gmail.com>
 * @package Moss\Storage
 */
class ReadQuery implements ReadInterface
{
    const AGGREGATE_DISTINCT = 'distinct';
    const AGGREGATE_COUNT = 'count';
    const AGGREGATE_AVERAGE = 'average';
    const AGGREGATE_MAX = 'max';
    const AGGREGATE_MIN = 'min';
    const AGGREGATE_SUM = 'sum';

    const COMPARISON_EQUAL = '=';
    const COMPARISON_NOT_EQUAL = '!=';
    const COMPARISON_LESS = '<';
    const COMPARISON_LESS_OR_EQUAL = '<=';
    const COMPARISON_GREATER = '>';
    const COMPARISON_GREATER_OR_EQUAL = '>=';
    const COMPARISON_LIKE = 'like';
    const COMPARISON_REGEXP = 'regexp';

    const LOGICAL_AND = 'and';
    const LOGICAL_OR = 'or';

    const ORDER_ASC = 'asc';
    const ORDER_DESC = 'desc';

    /**
     * @var Connection
     */
    protected $connection;

    /**
     * @var ModelInterface
     */
    protected $model;

    /**
     * @var ConverterInterface
     */
    protected $converter;

    /**
     * @var RelationFactoryInterface
     */
    protected $factory;

    /**
     * @var QueryBuilder
     */
    protected $query;

    /**
     * @var array|object
     */
    protected $instance;

    /**
     * @var array|RelationInterface[]
     */
    protected $relations = [];

    /**
     * @var array
     */
    protected $casts = [];

    /**
     * @var array
     */
    protected $binds = [];

    /**
     * Constructor
     *
     * @param Connection               $connection
     * @param ModelInterface           $model
     * @param ConverterInterface       $converter
     * @param RelationFactoryInterface $factory
     */
    public function __construct(Connection $connection, ModelInterface $model, ConverterInterface $converter, RelationFactoryInterface $factory)
    {
        $this->connection = $connection;
        $this->model = $model;
        $this->converter = $converter;
        $this->factory = $factory;

        $this->setQuery();
    }

    /**
     * Sets query instance with delete operation and table
     */
    protected function setQuery()
    {
        $this->query = $this->connection->createQueryBuilder();
        $this->query->select();
        $this->query->from($this->connection->quoteIdentifier($this->model->table()));
        $this->fields();
    }

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
     * Sets field names which will be read
     *
     * @param array $fields
     *
     * @return $this
     */
    public function fields($fields = [])
    {
        $this->query->select([]);
        $this->casts = [];

        if (empty($fields)) {
            foreach ($this->model->fields() as $field) {
                $this->assignField($field);
            }

            return $this;
        }

        foreach ($fields as $field) {
            $this->assignField($this->model->field($field));
        }

        return $this;
    }

    /**
     * Adds field to query
     *
     * @param string $field
     *
     * @return $this
     */
    public function field($field)
    {
        $this->assignField($this->model->field($field));

        return $this;
    }

    /**
     * Assigns field to query
     *
     * @param FieldInterface $field
     */
    protected function assignField(FieldInterface $field)
    {
        if ($field->mapping()) {
            $this->query->addSelect(
                sprintf(
                    '%s AS %s',
                    $this->connection->quoteIdentifier($field->mapping()),
                    $this->connection->quoteIdentifier($field->name())
                )
            );
        } else {
            $this->query->addSelect($this->connection->quoteIdentifier($field->name()));
        }

        $this->casts[$field->name()] = $field->type();
    }

    /**
     * Adds distinct method to query
     *
     * @param string $field
     * @param string $alias
     *
     * @return $this
     */
    public function distinct($field, $alias = null)
    {
        $this->aggregate(self::AGGREGATE_DISTINCT, $field, $alias);

        return $this;
    }

    /**
     * Adds count method to query
     *
     * @param string $field
     * @param string $alias
     *
     * @return $this
     */
    public function count($field, $alias = null)
    {
        $this->aggregate(self::AGGREGATE_COUNT, $field, $alias);

        return $this;
    }

    /**
     * Adds average method to query
     *
     * @param string $field
     * @param string $alias
     *
     * @return $this
     */
    public function average($field, $alias = null)
    {
        $this->aggregate(self::AGGREGATE_AVERAGE, $field, $alias);

        return $this;
    }

    /**
     * Adds max method to query
     *
     * @param string $field
     * @param string $alias
     *
     * @return $this
     */
    public function max($field, $alias = null)
    {
        $this->aggregate(self::AGGREGATE_MAX, $field, $alias);

        return $this;
    }

    /**
     * Adds min method to query
     *
     * @param string $field
     * @param string $alias
     *
     * @return $this
     */
    public function min($field, $alias = null)
    {
        $this->aggregate(self::AGGREGATE_MIN, $field, $alias);

        return $this;
    }

    /**
     * Adds sum method to query
     *
     * @param string $field
     * @param string $alias
     *
     * @return $this
     */
    public function sum($field, $alias = null)
    {
        $this->aggregate(self::AGGREGATE_SUM, $field, $alias);

        return $this;
    }

    /**
     * Adds aggregate method to query
     *
     * @param string $method
     * @param string $field
     * @param string $alias
     *
     * @return $this
     * @throws QueryException
     */
    public function aggregate($method, $field, $alias = null)
    {
        $this->assertAggregate($method);

        $field = $this->model->field($field);
        $alias = $alias ?: strtolower($method);

        $this->query->addSelect(
            sprintf(
                '%s(%s) AS %s',
                $method,
                $this->connection->quoteIdentifier($field->mapping() ? $field->mapping() : $field->name()),
                $this->connection->quoteIdentifier($alias)
            )
        );

        return $this;
    }

    /**
     * Asserts if aggregate method is supported
     *
     * @param string $method
     *
     * @throws QueryException
     */
    protected function assertAggregate($method)
    {
        $aggregateMethods = [
            self::AGGREGATE_DISTINCT,
            self::AGGREGATE_COUNT,
            self::AGGREGATE_AVERAGE,
            self::AGGREGATE_MIN,
            self::AGGREGATE_MAX,
            self::AGGREGATE_SUM
        ];

        if (!in_array($method, $aggregateMethods)) {
            throw new QueryException(sprintf('Invalid aggregation method "%s" in query', $method, $this->model->entity()));
        }
    }

    /**
     * Adds grouping to query
     *
     * @param string $field
     *
     * @return $this
     */
    public function group($field)
    {
        $field = $this->model->field($field);

        $this->query->addGroupBy($this->connection->quoteIdentifier($field->mapping() ? $field->mapping() : $field->name()));

        return $this;
    }

    /**
     * Adds where condition to builder
     *
     * @param mixed  $field
     * @param mixed  $value
     * @param string $comparison
     * @param string $logical
     *
     * @return $this
     * @throws QueryException
     */
    public function where($field, $value, $comparison = self::COMPARISON_EQUAL, $logical = self::LOGICAL_AND)
    {
        $condition = $this->condition($field, $value, $comparison, $logical);

        if ($logical === self::LOGICAL_OR) {
            $this->query->orWhere($condition);

            return $this;
        }

        $this->query->andWhere($condition);

        return $this;
    }

    /**
     * Adds having condition to builder
     *
     * @param mixed  $field
     * @param mixed  $value
     * @param string $comparison
     * @param string $logical
     *
     * @return $this
     */
    public function having($field, $value, $comparison = '==', $logical = 'and')
    {
        $condition = $this->condition($field, $value, $comparison, $logical);

        if ($logical === self::LOGICAL_OR) {
            $this->query->orHaving($condition);

            return $this;
        }

        $this->query->andHaving($condition);

        return $this;
    }

    /**
     * Adds where condition to builder
     *
     * @param mixed  $field
     * @param mixed  $value
     * @param string $comparison
     * @param string $logical
     *
     * @return $this
     * @throws QueryException
     */
    public function condition($field, $value, $comparison, $logical)
    {
        $comparison = strtolower($comparison);
        $logical = strtolower($logical);

        $this->assertComparison($comparison);
        $this->assertLogical($logical);

        if (!is_array($field)) {
            return $this->buildSingularFieldCondition($field, $value, $comparison);
        }

        return $this->buildMultipleFieldsCondition($field, $value, $comparison, $logical);
    }

    /**
     * Builds condition for singular field
     *
     * @param string $field
     * @param mixed  $value
     * @param string $comparison
     *
     * @return array
     */
    protected function buildSingularFieldCondition($field, $value, $comparison)
    {
        $f = $this->model->field($field);

        $fieldName = $f->mapping() ? $f->mapping() : $f->name();

        return $this->buildConditionString(
            $this->connection->quoteIdentifier($fieldName),
            $value === null ? null : $this->bindValues($fieldName, $f->type(), $value),
            $comparison
        );
    }

    /**
     * Builds conditions for multiple fields
     *
     * @param array  $field
     * @param mixed  $value
     * @param string $comparison
     * @param string $logical
     *
     * @return array
     */
    protected function buildMultipleFieldsCondition($field, $value, $comparison, $logical)
    {
        $conditions = [];
        foreach ((array) $field as $i => $f) {
            $f = $this->model->field($f);

            $fieldName = $f->mapping() ? $f->mapping() : $f->name();
            $conditions[] = $this->buildConditionString(
                $this->connection->quoteIdentifier($fieldName),
                $value === null ? null : $this->bindValues($fieldName, $f->type(), $value),
                $comparison
            );

            $conditions[] = $logical;
        }

        array_pop($conditions);

        return '(' . implode(' ', $conditions) . ')';
    }

    /**
     * Builds condition string
     *
     * @param string       $field
     * @param string|array $bind
     * @param string       $operator
     *
     * @return string
     */
    protected function buildConditionString($field, $bind, $operator)
    {
        if (is_array($bind)) {
            foreach ($bind as &$val) {
                $val = $this->buildConditionString($field, $val, $operator);
                unset($val);
            }

            $operator = $operator === self::COMPARISON_NOT_EQUAL ? 'and' : 'or';

            return '(' . implode(sprintf(' %s ', $operator), $bind) . ')';
        }

        if ($bind === null) {
            return $field . ' ' . ($operator == '!=' ? 'IS NOT NULL' : 'IS NULL');
        }

        if ($operator === self::COMPARISON_REGEXP) {
            return sprintf('%s regexp %s', $field, $bind);
        }

        return $field . ' ' . $operator . ' ' . $bind;
    }

    /**
     * Asserts correct comparison operator
     *
     * @param string $operator
     *
     * @throws QueryException
     */
    protected function assertComparison($operator)
    {
        $comparisonOperators = [
            self::COMPARISON_EQUAL,
            self::COMPARISON_NOT_EQUAL,
            self::COMPARISON_LESS,
            self::COMPARISON_LESS_OR_EQUAL,
            self::COMPARISON_GREATER,
            self::COMPARISON_GREATER_OR_EQUAL,
            self::COMPARISON_LIKE,
            self::COMPARISON_REGEXP
        ];

        if (!in_array($operator, $comparisonOperators)) {
            throw new QueryException(sprintf('Query does not supports comparison operator "%s" in query "%s"', $operator, $this->model->entity()));
        }
    }

    /**
     * Asserts correct logical operation
     *
     * @param string $operator
     *
     * @throws QueryException
     */
    protected function assertLogical($operator)
    {
        $comparisonOperators = [
            self::LOGICAL_AND,
            self::LOGICAL_OR
        ];

        if (!in_array($operator, $comparisonOperators)) {
            throw new QueryException(sprintf('Query does not supports logical operator "%s" in query "%s"', $operator, $this->model->entity()));
        }
    }

    /**
     * Binds condition value to key
     *
     * @param $name
     * @param $type
     * @param $values
     *
     * @return array|string
     */
    protected function bindValues($name, $type, $values)
    {
        if (!is_array($values)) {
            return $this->bind('condition', $name, $type, $values);
        }

        foreach ($values as $key => $value) {
            $values[$key] = $this->bindValues($name, $type, $value);
        }

        return $values;
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
        $key = ':' . implode('_', [$operation, count($this->binds), $field]);
        $this->binds[$key] = $this->converter->store($value, $type);

        return $key;
    }

    /**
     * Adds sorting to query
     *
     * @param string       $field
     * @param string|array $order
     *
     * @return $this
     */
    public function order($field, $order = self::ORDER_DESC)
    {
        $field = $this->model->field($field);

        $this->assertOrder($order);

        $field = $field->mapping() ? $field->mapping() : $field->name();
        $this->query->addOrderBy($this->connection->quoteIdentifier($field), $order);

        return $this;
    }

    /**
     * Asserts correct order
     *
     * @param string|array $order
     *
     * @throws QueryException
     */
    protected function assertOrder($order)
    {
        if (!in_array($order, [self::ORDER_ASC, self::ORDER_DESC])) {
            throw new QueryException(sprintf('Unsupported sorting method "%s" in query "%s"', is_scalar($order) ? $order : gettype($order), $this->model->entity()));
        }
    }

    /**
     * Sets limits to query
     *
     * @param int      $limit
     * @param null|int $offset
     *
     * @return $this
     */
    public function limit($limit, $offset = null)
    {
        if ($offset) {
            $this->query->setFirstResult((int) $offset);
        }

        $this->query->setMaxResults((int) $limit);

        return $this;
    }


    /**
     * Adds relation to query with optional conditions and sorting (as key value pairs)
     *
     * @param string|array $relation
     * @param array        $conditions
     * @param array        $order
     *
     * @return $this
     * @throws QueryException
     */
    public function with($relation, array $conditions = [], array $order = [])
    {
        $instance = $this->factory->create($this->model, $relation, $conditions, $order);
        $this->relations[$instance->name()] = $instance;

        return $this;
    }

    /**
     * Returns relation instance
     *
     * @param string $relation
     *
     * @return RelationInterface
     * @throws QueryException
     */
    public function relation($relation)
    {
        list($relation, $furtherRelations) = $this->factory->splitRelationName($relation);

        if (!isset($this->relations[$relation])) {
            throw new QueryException(sprintf('Unable to retrieve relation "%s" query, relation does not exists in query "%s"', $relation, $this->model->entity()));
        }

        $instance = $this->relations[$relation];

        if ($furtherRelations) {
            return $instance->relation($furtherRelations);
        }

        return $instance;
    }

    /**
     * Executes query
     * After execution query is reset
     *
     * @return mixed|null|void
     */
    public function execute()
    {
        $stmt = $this->connection->prepare($this->queryString());
        $stmt->execute($this->binds);

        $result = $stmt->fetchAll(\PDO::FETCH_CLASS, $this->model->entity());

        $ref = new \ReflectionClass($this->model->entity());
        foreach ($result as $entity) {
            $this->restoreObject($entity, $this->casts, $ref);
        }

        foreach ($this->relations as $relation) {
            $result = $relation->read($result);
        }

        return $result;
    }

    /**
     * Restores entity values from their stored representation
     *
     * @param object           $entity
     * @param array            $restore
     * @param \ReflectionClass $ref
     *
     * @return mixed
     */
    protected function restoreObject($entity, array $restore, \ReflectionClass $ref)
    {
        foreach ($restore as $field => $type) {
            if(is_array($entity)) {
                if(!isset($entity[$field])) {
                    continue;
                }

                $entity[$field] = $this->converter->restore($entity[$field], $type);
                continue;
            }

            if (!$ref->hasProperty($field)) {
                if(!isset($entity->$field)) {
                    continue;
                }

                $entity->$field = $this->converter->restore($entity->$field, $type);
                continue;
            }

            $prop = $ref->getProperty($field);
            $prop->setAccessible(true);

            $value = $prop->getValue($entity);
            $value = $this->converter->restore($value, $type);
            $prop->setValue($entity, $value);
        }

        return $entity;
    }

     /**
     * Returns current query string
     *
     * @return string
     */
    public function queryString()
    {
        return (string) $this->query->getSQL();
    }

    /**
     * Returns array with bound values and their placeholders as keys
     *
     * @return array
     */
    public function binds()
    {
        return $this->binds;
    }

    /**
     * Resets adapter
     *
     * @return $this
     */
    public function reset()
    {
        $this->query->resetQueryParts();
        $this->relations = [];
        $this->casts = [];
        $this->binds = [];

        $this->setQuery();

        return $this;
    }
}
